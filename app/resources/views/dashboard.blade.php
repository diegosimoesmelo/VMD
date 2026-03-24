@extends('layouts.panel', ['title' => 'Inicio'])

@section('content')
    <h1>Inicio</h1>
    <p>Bem-vindo, {{ auth()->user()->username }}.</p>
    <p>Use o menu lateral para acessar cadastro de alunos, professores e agendamentos.</p>
@endsection
