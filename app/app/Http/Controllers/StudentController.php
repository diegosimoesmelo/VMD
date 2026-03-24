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
    public function index(): View
    {
        $students = Student::query()
            ->with('teacher')
            ->orderBy('nome')
            ->get();

        return view('students.index', compact('students'));
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
            'servico_oferecido' => ['nullable', 'in:primeira_habilitacao,adicao_categoria,aula_habilitado'],
            'categoria_pretendida' => ['nullable', 'in:A,B,AB'],
            'valor_pago' => ['nullable', 'numeric', 'min:0'],
            'observacao' => ['nullable', 'string'],
        ];
    }
}
