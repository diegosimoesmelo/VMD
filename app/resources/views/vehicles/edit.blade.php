@extends('layouts.panel', ['title' => 'Editar veÃ­culo'])

@section('content')
    @include('vehicles._vehicle_form', [
        'formAction' => route('vehicles.update', $vehicle),
        'formMethod' => 'PUT',
        'submitLabel' => 'Atualizar veÃ­culo',
        'backUrl' => route('vehicles.index'),
        'vehicle' => $vehicle,
    ])
@endsection

