<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = Teacher::query()
            ->orderBy('nome')
            ->get();

        return view('teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('teachers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Teacher::create($validated);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Professor cadastrado com sucesso.');
    }

    public function edit(Teacher $teacher): View
    {
        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate($this->rules($teacher));

        $teacher->update($validated);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Dados do professor atualizados com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Teacher $teacher = null): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => [
                'required',
                'string',
                'max:20',
                Rule::unique('teachers', 'cpf')->ignore($teacher?->id),
            ],
            'telefone' => ['required', 'string', 'max:30'],
            'categorias_ensino' => ['required', 'array', 'min:1'],
            'categorias_ensino.*' => ['required', 'string', Rule::in(['A', 'B', 'C', 'D', 'E', 'AB'])],
            'turnos_disponiveis' => ['required', 'array', 'min:1'],
            'turnos_disponiveis.*' => ['required', 'string', Rule::in(['manha', 'tarde', 'noite', 'integral'])],
        ];
    }
}
