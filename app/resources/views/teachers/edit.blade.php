@extends('layouts.panel', ['title' => 'Editar professor'])

@section('content')
    <h1>Editar professor</h1>
    <p>Atualize os dados do professor.</p>

    @include('teachers._teacher_form', [
        'formAction' => route('teachers.update', $teacher),
        'formMethod' => 'PUT',
        'teacher' => $teacher,
        'submitLabel' => 'Atualizar professor',
        'backUrl' => route('teachers.index'),
    ])
@endsection
