<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::query()
            ->orderBy('placa')
            ->get();

        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Vehicle::create($validated);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Veiculo cadastrado com sucesso.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate($this->rules($vehicle));

        $vehicle->update($validated);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Dados do veiculo atualizados com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Vehicle $vehicle = null): array
    {
        return [
            'placa' => [
                'required',
                'string',
                'max:10',
                Rule::unique('vehicles', 'placa')->ignore($vehicle?->id),
            ],
            'categoria' => ['required', 'string', Rule::in(array_keys(Vehicle::categoryOptions()))],
        ];
    }
}
