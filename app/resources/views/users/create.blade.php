@extends('layouts.panel', ['title' => 'Cadastrar usuÃ¡rio'])

@section('content')
    @include('users._form', [
        'formAction' => route('users.store'),
        'formMethod' => 'POST',
        'user' => null,
        'submitLabel' => 'Salvar usuÃ¡rio',
        'backUrl' => route('users.index'),
    ])
@endsection

