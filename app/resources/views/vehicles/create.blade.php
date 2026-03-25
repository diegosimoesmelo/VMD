@extends('layouts.panel', ['title' => 'Cadastrar veiculo'])

@section('content')
    @include('vehicles._vehicle_form', [
        'formAction' => route('vehicles.store'),
        'submitLabel' => 'Salvar veiculo',
        'backUrl' => route('vehicles.index'),
    ])
@endsection
