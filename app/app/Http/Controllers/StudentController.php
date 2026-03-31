<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $student = Student::create($validated);
        $student->matricula = Student::gerarMatricula((int) $student->id);
        $student->save();

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

        $student->update($validated);

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
            'servico_oferecido' => ['nullable', 'in:primeira_habilitacao,adicao_categoria,aula_habilitado'],
            'categoria_pretendida' => ['nullable', 'in:A,B,AB'],
            'valor_pago' => ['nullable', 'numeric', 'min:0'],
            'observacao' => ['nullable', 'string'],
        ];
    }
}
