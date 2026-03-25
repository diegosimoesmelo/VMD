<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_vehicle_creation_screen(): void
    {
        $user = User::factory()->create();
        Teacher::query()->create([
            'nome' => 'Professor Veiculo',
            'cpf' => '123.123.123-12',
            'telefone' => '(81) 98888-0000',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('vehicles.create'));

        $response->assertOk();
        $response->assertSee('Cadastro de veiculo');
    }

    public function test_authenticated_user_can_store_vehicle(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor Frota',
            'cpf' => '456.456.456-45',
            'telefone' => '(81) 97777-0000',
            'categorias_ensino' => ['A', 'B'],
            'turnos_disponiveis' => ['manha', 'tarde'],
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('vehicles.store'), [
                'teacher_id' => $teacher->id,
                'placa' => 'ABC1D23',
                'categoria' => 'B',
            ]);

        $response->assertRedirect(route('vehicles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'teacher_id' => $teacher->id,
            'placa' => 'ABC1D23',
            'categoria' => 'B',
        ]);
    }
}
