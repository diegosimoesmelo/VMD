<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_teacher_creation_screen(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('teachers.create'));

        $response->assertOk();
        $response->assertSee('Cadastrar professor');
    }

    public function test_authenticated_user_can_store_teacher(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('teachers.store'), [
                'nome' => 'Maria Silva',
                'cpf' => '123.456.789-10',
                'telefone' => '(81) 98888-7777',
                'categorias_ensino' => ['A', 'B'],
                'turnos_disponiveis' => ['manha', 'tarde'],
            ]);

        $response->assertRedirect(route('teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'nome' => 'Maria Silva',
            'cpf' => '123.456.789-10',
            'telefone' => '(81) 98888-7777',
        ]);
    }
}
