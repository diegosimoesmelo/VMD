<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString() ?: 'active';
        $teacherFilter = $request->string('teacher_id')->toString();
        $timelineStatusFilter = $request->string('timeline_status')->toString();
        $teachers = Teacher::query()
            ->orderBy('nome')
            ->get();

        $baseQuery = Student::query()
            ->with([
                'teacher',
                'appointments' => fn ($query) => $query
                    ->with(['teacher', 'vehicle'])
                    ->orderByDesc('starts_at'),
                'lessonPurchases' => fn ($query) => $query
                    ->with('user')
                    ->orderByDesc('purchased_at')
                    ->orderByDesc('id'),
            ])
            ->orderBy('nome');

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));

            $baseQuery->where(function ($query) use ($search) {
                $query
                    ->where('nome', 'like', '%'.$search.'%')
                    ->orWhere('cpf', 'like', '%'.$search.'%');
            });
        }

        if ($teacherFilter !== '') {
            if ($teacherFilter === 'without_teacher') {
                $baseQuery->whereNull('teacher_id');
            } else {
                $baseQuery->where('teacher_id', (int) $teacherFilter);
            }
        }

        if ($timelineStatusFilter !== '') {
            $baseQuery->where('status', $timelineStatusFilter);
        }

        $tabCounts = [
            'active' => (clone $baseQuery)->where('status', '!=', Student::STATUS_FINISHED)->count(),
            'without_teacher' => (clone $baseQuery)->whereNull('teacher_id')->count(),
            'finished' => (clone $baseQuery)->where('status', Student::STATUS_FINISHED)->count(),
        ];

        $studentsQuery = clone $baseQuery;

        if ($tab === 'without_teacher') {
            $studentsQuery->whereNull('teacher_id');
        } elseif ($tab === 'finished') {
            $studentsQuery->where('status', Student::STATUS_FINISHED);
        } else {
            $studentsQuery->where('status', '!=', Student::STATUS_FINISHED);
        }

        $students = $studentsQuery->get();
        $students->each->syncRemainingLessons();

        return view('students.index', [
            'students' => $students,
            'teachers' => $teachers,
            'tabCounts' => $tabCounts,
            'filters' => [
                'tab' => $tab,
                'search' => $request->string('search')->toString(),
                'teacher_id' => $teacherFilter,
                'timeline_status' => $timelineStatusFilter,
            ],
        ]);
    }

    public function create(): View
    {
        $teachers = Teacher::query()
            ->orderBy('nome')
            ->get();

        return view('students.create', compact('teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated = $this->normalizeStudentLessonQuantities($validated);

        $student = Student::create($validated);
        $student->matricula = Student::gerarMatricula((int) $student->id);
        $student->save();
        $student->syncRemainingLessons();

        if (($student->valor_pago ?? 0) > 0) {
            return redirect()
                ->route('students.receipts.registration.show', $student)
                ->with('success', 'Aluno cadastrado com sucesso. Recibo gerado automaticamente.');
        }

        return redirect()
            ->route('students.index')
            ->with('success', 'Aluno cadastrado com sucesso. Matricula: '.$student->matricula);
    }

    public function edit(Student $student): View
    {
        $teachers = Teacher::query()
            ->orderBy('nome')
            ->get();

        return view('students.edit', compact('student', 'teachers'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate($this->rules($student));
        $validated = $this->normalizeStudentLessonQuantities($validated);

        $student->update($validated);
        $student->syncRemainingLessons();

        return redirect()
            ->route('students.index')
            ->with('success', 'Dados do aluno atualizados com sucesso.');
    }

    public function advanceStatus(Request $request, Student $student): RedirectResponse
    {
        $nextStatus = $student->nextStatus();

        if (! $nextStatus) {
            return redirect()
                ->route('students.index', $request->only(['tab', 'search', 'teacher_id', 'timeline_status']))
                ->with('success', 'O aluno ja esta na etapa final.');
        }

        $student->update(['status' => $nextStatus]);

        return redirect()
            ->route('students.index', $request->only(['tab', 'search', 'teacher_id', 'timeline_status']))
            ->with('success', 'Status do aluno atualizado para '.$student->statusLabel().'.');
    }

    public function storeLessonPurchase(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'lesson_category' => ['required', Rule::in(['A', 'B'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'required_with:amount_paid', Rule::in(array_keys(config('receipt.payment_methods')))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tab' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'teacher_id' => ['nullable', 'string'],
            'timeline_status' => ['nullable', 'string'],
        ]);

        if (! in_array($validated['lesson_category'], $this->allowedLessonCategories($student->categoria_pretendida), true)) {
            return redirect()
                ->route('students.index', $request->only(['tab', 'search', 'teacher_id', 'timeline_status']))
                ->withErrors(['lesson_category' => 'A categoria da compra nao e compativel com o cadastro do aluno.']);
        }

        $purchase = null;

        DB::transaction(function () use ($student, $validated, &$purchase) {
            $student = Student::query()
                ->whereKey($student->id)
                ->lockForUpdate()
                ->firstOrFail();

            $purchase = $student->lessonPurchases()->create([
                'user_id' => auth()->id(),
                'lesson_category' => $validated['lesson_category'],
                'quantity' => $validated['quantity'],
                'amount_paid' => $validated['amount_paid'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'purchased_at' => now(),
            ]);

            $field = $validated['lesson_category'] === 'A'
                ? 'quantidade_aulas_a_contratadas'
                : 'quantidade_aulas_b_contratadas';

            $student->forceFill([
                $field => ((int) ($student->{$field} ?? 0)) + (int) $validated['quantity'],
            ])->save();

            $student->refresh();
            $student->syncRemainingLessons();
        });

        if ($purchase && $purchase->amount_paid !== null) {
            return redirect()
                ->route('lesson-purchases.receipts.show', $purchase)
                ->with('success', 'Compra registrada com sucesso. Recibo gerado automaticamente.');
        }

        return redirect()
            ->route('students.index', $request->only(['tab', 'search', 'teacher_id', 'timeline_status']))
            ->with('success', 'Compra de aulas registrada para '.$student->nome.'.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Student $student = null): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'endereco' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:30'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'bairro' => ['nullable', 'string', 'max:120'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:20'],
            'telefone' => ['required', 'string', 'max:30'],
            'data_nascimento' => ['required', 'date'],
            'sexo' => ['nullable', 'string', 'max:30'],
            'naturalidade' => ['nullable', 'string', 'max:120'],
            'naturalidade_estado' => ['nullable', 'string', 'size:2'],
            'nacionalidade' => ['nullable', 'string', 'max:120'],
            'rg' => ['nullable', 'string', 'max:30'],
            'orgao_exp' => ['nullable', 'string', 'max:60'],
            'rg_estado' => ['nullable', 'string', 'size:2'],
            'cpf' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'cpf')->ignore($student?->id),
            ],
            'estado_civil' => ['nullable', 'string', 'max:40'],
            'grau_escolaridade' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'profissao' => ['nullable', 'string', 'max:120'],
            'telefone_profissional' => ['nullable', 'string', 'max:30'],
            'nome_pai' => ['nullable', 'string', 'max:255'],
            'nome_mae' => ['required', 'string', 'max:255'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'status' => ['required', Rule::in(array_keys(Student::statusOptions()))],
            'servico_oferecido' => ['nullable', 'in:primeira_habilitacao,adicao_categoria,aula_habilitado,prova_atualizacao,prova_reciclagem'],
            'categoria_pretendida' => ['nullable', 'in:A,B,AB'],
            'valor_pago' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'required_with:valor_pago', Rule::in(array_keys(config('receipt.payment_methods')))],
            'quantidade_aulas_a_contratadas' => ['nullable', 'integer', 'min:0'],
            'quantidade_aulas_b_contratadas' => ['nullable', 'integer', 'min:0'],
            'observacao' => ['nullable', 'string'],
        ];
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function normalizeStudentLessonQuantities(array $validated): array
    {
        $category = $validated['categoria_pretendida'] ?? null;
        $lessonsA = (int) ($validated['quantidade_aulas_a_contratadas'] ?? 0);
        $lessonsB = (int) ($validated['quantidade_aulas_b_contratadas'] ?? 0);

        if ($category === 'A' && $lessonsB > 0) {
            throw ValidationException::withMessages([
                'quantidade_aulas_b_contratadas' => 'Aluno da categoria A nao pode ter aulas B contratadas.',
            ]);
        }

        if ($category === 'B' && $lessonsA > 0) {
            throw ValidationException::withMessages([
                'quantidade_aulas_a_contratadas' => 'Aluno da categoria B nao pode ter aulas A contratadas.',
            ]);
        }

        if (! in_array($category, ['A', 'B', 'AB'], true) && ($lessonsA > 0 || $lessonsB > 0)) {
            throw ValidationException::withMessages([
                'categoria_pretendida' => 'Selecione a categoria do aluno antes de informar aulas contratadas.',
            ]);
        }

        if ($category === 'A') {
            $validated['quantidade_aulas_b_contratadas'] = null;
        } elseif ($category === 'B') {
            $validated['quantidade_aulas_a_contratadas'] = null;
        } elseif (! in_array($category, ['A', 'B', 'AB'], true)) {
            $validated['quantidade_aulas_a_contratadas'] = null;
            $validated['quantidade_aulas_b_contratadas'] = null;
        }

        return $validated;
    }

    /**
     * @return list<string>
     */
    private function allowedLessonCategories(?string $studentCategory): array
    {
        return match ($studentCategory) {
            'A' => ['A'],
            'B' => ['B'],
            'AB' => ['A', 'B'],
            default => [],
        };
    }
}
