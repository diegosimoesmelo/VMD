<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_vehicle_creation_screen(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('vehicles.create'));

        $response->assertOk();
        $response->assertSee('Cadastro de veiculo');
    }

    public function test_authenticated_user_can_store_vehicle(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('vehicles.store'), [
                'placa' => 'ABC1D23',
                'categoria' => 'B',
            ]);

        $response->assertRedirect(route('vehicles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'placa' => 'ABC1D23',
            'categoria' => 'B',
        ]);
    }
}
