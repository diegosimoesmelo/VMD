@extends('layouts.panel', ['title' => 'Editar veiculo'])

@section('content')
    @include('vehicles._vehicle_form', [
        'formAction' => route('vehicles.update', $vehicle),
        'formMethod' => 'PUT',
        'submitLabel' => 'Atualizar veiculo',
        'backUrl' => route('vehicles.index'),
        'vehicle' => $vehicle,
        'teachers' => $teachers,
    ])
@endsection
