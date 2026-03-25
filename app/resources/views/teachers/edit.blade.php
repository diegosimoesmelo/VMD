@extends('layouts.panel', ['title' => 'Editar professor'])

@section('content')
    @include('teachers._teacher_form', [
        'formAction' => route('teachers.update', $teacher),
        'formMethod' => 'PUT',
        'teacher' => $teacher,
        'submitLabel' => 'Atualizar professor',
        'backUrl' => route('teachers.index'),
    ])
@endsection
