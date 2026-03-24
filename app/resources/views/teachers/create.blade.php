@extends('layouts.panel', ['title' => 'Cadastrar professor'])

@section('content')
    <h1>Cadastrar professor</h1>
    <p>Preencha os dados do professor.</p>

    @include('teachers._teacher_form', [
        'formAction' => route('teachers.store'),
        'formMethod' => 'POST',
        'teacher' => null,
        'submitLabel' => 'Salvar professor',
        'backUrl' => route('teachers.index'),
    ])
@endsection
