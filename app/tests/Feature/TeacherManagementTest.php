<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Vehicle;
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

    public function test_teacher_list_shows_weekly_schedule_modal_content(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor Grade',
            'cpf' => '123.123.123-12',
            'telefone' => '(81) 98888-1111',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicle = Vehicle::query()->create([
            'placa' => 'MOD1A23',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Aluno da Grade',
            'endereco' => 'Rua 1',
            'telefone' => '(81) 97777-1111',
            'data_nascimento' => '2000-01-01',
            'cpf' => '321.321.321-32',
            'nome_mae' => 'Mae da Grade',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        Appointment::query()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'vehicle_id' => $vehicle->id,
            'type' => Appointment::TYPE_LESSON,
            'lesson_category' => 'B',
            'starts_at' => '2026-03-23 07:00:00',
            'ends_at' => '2026-03-23 07:50:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('teachers.index', ['week_start' => '2026-03-23']));

        $response->assertOk();
        $response->assertSee('Resumo semanal');
        $response->assertSee('Aluno da Grade');
        $response->assertSee('MOD1A23');
        $response->assertSee('07:00');
    }
}
