@extends('layouts.panel', ['title' => 'Editar usuario'])

@section('content')
    @include('users._form', [
        'formAction' => route('users.update', $user),
        'formMethod' => 'PUT',
        'user' => $user,
        'submitLabel' => 'Salvar alteracoes',
        'backUrl' => route('users.index'),
    ])
@endsection
