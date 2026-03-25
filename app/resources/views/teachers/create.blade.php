@extends('layouts.panel', ['title' => 'Cadastrar professor'])

@section('content')
    @include('teachers._teacher_form', [
        'formAction' => route('teachers.store'),
        'formMethod' => 'POST',
        'teacher' => null,
        'submitLabel' => 'Salvar professor',
        'backUrl' => route('teachers.index'),
    ])
@endsection
