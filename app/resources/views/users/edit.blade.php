@extends('layouts.panel', ['title' => 'Editar usuÃ¡rio'])

@section('content')
    @include('users._form', [
        'formAction' => route('users.update', $user),
        'formMethod' => 'PUT',
        'user' => $user,
        'submitLabel' => 'Salvar alteraÃ§Ãµes',
        'backUrl' => route('users.index'),
    ])
@endsection

