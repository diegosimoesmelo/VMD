@extends('layouts.panel', ['title' => 'Editar aluno'])

@section('content')
    <h1>Editar aluno</h1>
    <p>Atualize os dados do aluno.</p>

    @include('students._student_form', [
        'formAction' => route('students.update', $student),
        'formMethod' => 'PUT',
        'student' => $student,
        'teachers' => $teachers,
        'submitLabel' => 'Atualizar aluno',
        'backUrl' => route('students.index'),
    ])
@endsection
