@extends('layouts.panel', ['title' => 'Cadastrar aluno'])

@section('content')
    @include('students._student_form', [
        'formAction' => route('students.store'),
        'formMethod' => 'POST',
        'student' => null,
        'teachers' => $teachers,
        'submitLabel' => 'Salvar aluno',
        'backUrl' => null,
    ])
@endsection
