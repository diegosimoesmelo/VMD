@extends('layouts.panel', ['title' => 'Cadastrar aluno'])

@section('content')
    <h1>Cadastrar aluno</h1>
    <p>Preencha os dados do aluno por etapas.</p>

    @include('students._student_form', [
        'formAction' => route('students.store'),
        'formMethod' => 'POST',
        'student' => null,
        'teachers' => $teachers,
        'submitLabel' => 'Salvar aluno',
        'backUrl' => null,
    ])
@endsection
