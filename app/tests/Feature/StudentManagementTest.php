<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_student_timeline_in_list(): void
    {
        $user = User::factory()->create();
        Student::query()->create([
            'nome' => 'Marina Alves',
            'endereco' => 'Rua das Flores',
            'telefone' => '(81) 99999-1111',
            'data_nascimento' => '2000-05-10',
            'cpf' => '123.456.789-00',
            'nome_mae' => 'Carla Alves',
            'status' => Student::STATUS_THEORY_CLASS,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('students.index'));

        $response->assertOk();
        $response->assertSee('Linha do tempo operacional por aluno');
        $response->assertSee('Em aula teorica');
        $response->assertSee('Avancar etapa');
    }

    public function test_authenticated_user_can_advance_student_status_from_list(): void
    {
        $user = User::factory()->create();
        $student = Student::query()->create([
            'nome' => 'Paula Nascimento',
            'endereco' => 'Rua Central',
            'telefone' => '(81) 98888-2222',
            'data_nascimento' => '1999-11-20',
            'cpf' => '987.654.321-11',
            'nome_mae' => 'Lidia Nascimento',
            'status' => Student::STATUS_THEORY_CLASS,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('students.advance-status', $student), [
                'tab' => 'active',
                'search' => 'Paula',
            ]);

        $response->assertRedirect(route('students.index', [
            'tab' => 'active',
            'search' => 'Paula',
        ]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => Student::STATUS_THEORY_PASSED,
        ]);
    }
}
