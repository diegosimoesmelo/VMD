@extends('layouts.panel', ['title' => 'Cadastrar veÃ­culo'])

@section('content')
    @include('vehicles._vehicle_form', [
        'formAction' => route('vehicles.store'),
        'submitLabel' => 'Salvar veÃ­culo',
        'backUrl' => route('vehicles.index'),
    ])
@endsection

