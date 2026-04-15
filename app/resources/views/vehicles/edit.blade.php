@extends('layouts.panel', ['title' => 'Editar veículo'])

@section('content')
    @include('vehicles._vehicle_form', [
        'formAction' => route('vehicles.update', $vehicle),
        'formMethod' => 'PUT',
        'submitLabel' => 'Atualizar veículo',
        'backUrl' => route('vehicles.index'),
        'vehicle' => $vehicle,
    ])
@endsection

