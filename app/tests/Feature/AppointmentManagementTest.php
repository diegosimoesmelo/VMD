<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_weekly_schedule(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Joao Instrutor',
            'cpf' => '111.222.333-44',
            'telefone' => '(81) 98888-1111',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha', 'tarde'],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('appointments.index', ['teacher' => $teacher->id, 'week_start' => '2026-03-23']));

        $response->assertOk();
        $response->assertSee('Agenda de professores');
        $response->assertSee('Joao Instrutor');
    }

    public function test_schedule_student_select_lists_all_active_students(): void
    {
        $user = User::factory()->create();
        $selectedTeacher = Teacher::query()->create([
            'nome' => 'Sonia Instrutora',
            'cpf' => '111.111.111-11',
            'telefone' => '(81) 90000-0001',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $otherTeacher = Teacher::query()->create([
            'nome' => 'Carlos Instrutor',
            'cpf' => '222.222.222-22',
            'telefone' => '(81) 90000-0002',
            'categorias_ensino' => ['A'],
            'turnos_disponiveis' => ['tarde'],
        ]);

        Student::query()->create([
            'nome' => 'Aluno Vinculado',
            'endereco' => 'Rua 1',
            'telefone' => '(81) 91111-1111',
            'data_nascimento' => '2000-01-01',
            'cpf' => '100.100.100-10',
            'nome_mae' => 'Mae 1',
            'teacher_id' => $selectedTeacher->id,
            'status' => Student::STATUS_THEORY_PASSED,
            'categoria_pretendida' => 'B',
        ]);
        Student::query()->create([
            'nome' => 'Aluno Outro Professor',
            'endereco' => 'Rua 2',
            'telefone' => '(81) 92222-2222',
            'data_nascimento' => '2001-02-02',
            'cpf' => '200.200.200-20',
            'nome_mae' => 'Mae 2',
            'teacher_id' => $otherTeacher->id,
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'A',
        ]);
        Student::query()->create([
            'nome' => 'Aluno Sem Professor',
            'endereco' => 'Rua 3',
            'telefone' => '(81) 93333-3333',
            'data_nascimento' => '2002-03-03',
            'cpf' => '300.300.300-30',
            'nome_mae' => 'Mae 3',
            'teacher_id' => null,
            'status' => Student::STATUS_THEORY_CLASS,
            'categoria_pretendida' => 'AB',
        ]);
        Student::query()->create([
            'nome' => 'Aluno Finalizado',
            'endereco' => 'Rua 4',
            'telefone' => '(81) 94444-4444',
            'data_nascimento' => '2003-04-04',
            'cpf' => '400.400.400-40',
            'nome_mae' => 'Mae 4',
            'teacher_id' => null,
            'status' => Student::STATUS_FINISHED,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('appointments.index', ['teacher' => $selectedTeacher->id, 'week_start' => '2026-03-23']));

        $response->assertOk();
        $response->assertSee('Aluno Vinculado - Aula B - vinculado a este professor');
        $response->assertSee('Aluno Outro Professor - Aula A - professor: Carlos Instrutor');
        $response->assertSee('Aluno Sem Professor - Aula A ou B - sem professor');
        $response->assertDontSee('Aluno Finalizado');
    }

    public function test_authenticated_user_can_book_lesson_for_teacher_student_pair(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Maria Instrutora',
            'cpf' => '999.888.777-66',
            'telefone' => '(81) 97777-1111',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicle = Vehicle::query()->create([
            'teacher_id' => $teacher->id,
            'placa' => 'AAA1A11',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Carlos Souza',
            'endereco' => 'Rua A',
            'telefone' => '(81) 98888-2222',
            'data_nascimento' => '2000-01-01',
            'cpf' => '123.456.789-10',
            'nome_mae' => 'Ana Souza',
            'teacher_id' => $teacher->id,
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'lesson_category' => 'B',
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
                'notes' => 'Aula inaugural',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'vehicle_id' => $vehicle->id,
            'type' => Appointment::TYPE_LESSON,
            'lesson_category' => 'B',
            'notes' => 'Aula inaugural',
        ]);
    }

    public function test_booking_lesson_assigns_teacher_to_student_without_previous_link(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Paulo Instrutor',
            'cpf' => '555.444.333-22',
            'telefone' => '(81) 96666-1111',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['tarde'],
        ]);
        $vehicle = Vehicle::query()->create([
            'teacher_id' => $teacher->id,
            'placa' => 'BBB2B22',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Fernanda Lima',
            'endereco' => 'Rua B',
            'telefone' => '(81) 97777-2222',
            'data_nascimento' => '2001-02-02',
            'cpf' => '987.654.321-00',
            'nome_mae' => 'Marcia Lima',
            'teacher_id' => null,
            'status' => Student::STATUS_THEORY_PASSED,
            'categoria_pretendida' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'lesson_category' => 'B',
                'slot_date' => '2026-03-24',
                'slot_time' => '14:00',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_can_book_lesson_for_student_linked_to_another_teacher(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor Agendador',
            'cpf' => '444.555.666-77',
            'telefone' => '(81) 95555-0001',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicle = Vehicle::query()->create([
            'teacher_id' => $teacher->id,
            'placa' => 'CCC3C33',
            'categoria' => 'B',
        ]);
        $otherTeacher = Teacher::query()->create([
            'nome' => 'Professor Original',
            'cpf' => '888.777.666-55',
            'telefone' => '(81) 95555-0002',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['tarde'],
        ]);
        $student = Student::query()->create([
            'nome' => 'Aluno Compartilhado',
            'endereco' => 'Rua E',
            'telefone' => '(81) 94444-1111',
            'data_nascimento' => '2001-05-05',
            'cpf' => '741.852.963-00',
            'nome_mae' => 'Claudia Lima',
            'teacher_id' => $otherTeacher->id,
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'lesson_category' => 'B',
                'slot_date' => '2026-03-23',
                'slot_time' => '07:50',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'vehicle_id' => $vehicle->id,
            'lesson_category' => 'B',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'teacher_id' => $otherTeacher->id,
        ]);
    }

    public function test_cannot_book_lesson_for_finished_student(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Rita Instrutora',
            'cpf' => '101.202.303-40',
            'telefone' => '(81) 95555-1212',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicle = Vehicle::query()->create([
            'teacher_id' => $teacher->id,
            'placa' => 'DDD4D44',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Mateus Rocha',
            'endereco' => 'Rua C',
            'telefone' => '(81) 94444-5656',
            'data_nascimento' => '2002-03-03',
            'cpf' => '321.654.987-00',
            'nome_mae' => 'Lucia Rocha',
            'teacher_id' => $teacher->id,
            'status' => Student::STATUS_FINISHED,
            'categoria_pretendida' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('appointments.index', ['teacher' => $teacher->id, 'week_start' => '2026-03-23']))
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'lesson_category' => 'B',
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_book_lesson_outside_teacher_shift(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Tarde Somente',
            'cpf' => '909.808.707-60',
            'telefone' => '(81) 96666-0101',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['tarde'],
        ]);
        $vehicle = Vehicle::query()->create([
            'teacher_id' => $teacher->id,
            'placa' => 'EEE5E55',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Aluno Manha',
            'endereco' => 'Rua D',
            'telefone' => '(81) 93333-1212',
            'data_nascimento' => '2004-04-04',
            'cpf' => '654.987.321-00',
            'nome_mae' => 'Marta Silva',
            'teacher_id' => $teacher->id,
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('appointments.index', ['teacher' => $teacher->id, 'week_start' => '2026-03-23']))
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'lesson_category' => 'B',
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_book_same_vehicle_in_same_time_slot(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor Veiculo',
            'cpf' => '606.707.808-90',
            'telefone' => '(81) 93333-0001',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicle = Vehicle::query()->create([
            'teacher_id' => $teacher->id,
            'placa' => 'FFF6F66',
            'categoria' => 'B',
        ]);
        $firstStudent = Student::query()->create([
            'nome' => 'Primeiro Aluno',
            'endereco' => 'Rua F',
            'telefone' => '(81) 94444-0001',
            'data_nascimento' => '2005-06-06',
            'cpf' => '951.753.852-00',
            'nome_mae' => 'Marina Souza',
            'teacher_id' => $teacher->id,
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);
        $secondTeacher = Teacher::query()->create([
            'nome' => 'Outro Professor',
            'cpf' => '111.999.555-44',
            'telefone' => '(81) 95555-0009',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $secondStudent = Student::query()->create([
            'nome' => 'Segundo Aluno',
            'endereco' => 'Rua G',
            'telefone' => '(81) 95555-0003',
            'data_nascimento' => '2006-07-07',
            'cpf' => '159.357.258-00',
            'nome_mae' => 'Paula Souza',
            'teacher_id' => $secondTeacher->id,
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        $this
            ->actingAs($user)
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $firstStudent->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'lesson_category' => 'B',
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ])
            ->assertRedirect();

        $response = $this
            ->actingAs($user)
            ->from(route('appointments.index', ['teacher' => $secondTeacher->id, 'week_start' => '2026-03-23']))
            ->post(route('appointments.store'), [
                'teacher_id' => $secondTeacher->id,
                'student_id' => $secondStudent->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'lesson_category' => 'B',
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ]);

        $response->assertStatus(422);
    }
}
