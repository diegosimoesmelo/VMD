@extends('layouts.panel', ['title' => 'Cadastrar usuario'])

@section('content')
    @include('users._form', [
        'formAction' => route('users.store'),
        'formMethod' => 'POST',
        'user' => null,
        'submitLabel' => 'Salvar usuario',
        'backUrl' => route('users.index'),
    ])
@endsection
