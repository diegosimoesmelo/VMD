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

    public function test_authenticated_user_can_view_vehicle_weekly_schedule(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Joao Instrutor',
            'cpf' => '111.222.333-44',
            'telefone' => '(81) 98888-1111',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha', 'tarde'],
        ]);
        $vehicle = Vehicle::query()->create([
            'placa' => 'ABC1D23',
            'categoria' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('appointments.index', ['vehicle' => $vehicle->id, 'week_start' => '2026-03-23']));

        $response->assertOk();
        $response->assertSee('Agenda de veiculos');
        $response->assertSee('ABC1D23');
    }

    public function test_authenticated_user_can_book_lesson_from_vehicle_schedule(): void
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
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
                'notes' => 'Aula inaugural',
            ]);

        $response->assertRedirect(route('appointments.index', [
            'vehicle' => $vehicle->id,
            'week_start' => '2026-03-23',
        ]));
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

    public function test_vehicle_schedule_filters_students_by_vehicle_category(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Instrutor Moto',
            'cpf' => '111.111.111-11',
            'telefone' => '(81) 90000-0001',
            'categorias_ensino' => ['A'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicle = Vehicle::query()->create([
            'placa' => 'MOT0A11',
            'categoria' => 'A',
        ]);

        Student::query()->create([
            'nome' => 'Aluno Categoria A',
            'endereco' => 'Rua 1',
            'telefone' => '(81) 91111-1111',
            'data_nascimento' => '2000-01-01',
            'cpf' => '100.100.100-10',
            'nome_mae' => 'Mae 1',
            'status' => Student::STATUS_THEORY_PASSED,
            'categoria_pretendida' => 'A',
        ]);
        Student::query()->create([
            'nome' => 'Aluno Categoria AB',
            'endereco' => 'Rua 2',
            'telefone' => '(81) 92222-2222',
            'data_nascimento' => '2001-02-02',
            'cpf' => '200.200.200-20',
            'nome_mae' => 'Mae 2',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'AB',
        ]);
        Student::query()->create([
            'nome' => 'Aluno Categoria B',
            'endereco' => 'Rua 3',
            'telefone' => '(81) 93333-3333',
            'data_nascimento' => '2002-03-03',
            'cpf' => '300.300.300-30',
            'nome_mae' => 'Mae 3',
            'status' => Student::STATUS_THEORY_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('appointments.index', ['vehicle' => $vehicle->id, 'week_start' => '2026-03-23']));

        $response->assertOk();
        $response->assertSee('Aluno Categoria A - Aula A');
        $response->assertSee('Aluno Categoria AB - Aula A ou B');
        $response->assertDontSee('Aluno Categoria B - Aula B');
    }

    public function test_student_with_ab_category_can_book_a_and_b_lessons(): void
    {
        $user = User::factory()->create();
        $teacherA = Teacher::query()->create([
            'nome' => 'Professor Categoria A',
            'cpf' => '100.200.300-40',
            'telefone' => '(81) 90000-1001',
            'categorias_ensino' => ['A'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $teacherB = Teacher::query()->create([
            'nome' => 'Professor Categoria B',
            'cpf' => '500.600.700-80',
            'telefone' => '(81) 90000-1002',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicleA = Vehicle::query()->create([
            'placa' => 'MOT1A11',
            'categoria' => 'A',
        ]);
        $vehicleB = Vehicle::query()->create([
            'placa' => 'CAR2B22',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Aluno Categoria AB',
            'endereco' => 'Rua AB',
            'telefone' => '(81) 91111-2020',
            'data_nascimento' => '2000-05-05',
            'cpf' => '456.456.456-45',
            'nome_mae' => 'Mae AB',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'AB',
        ]);

        $this
            ->actingAs($user)
            ->post(route('appointments.store'), [
                'teacher_id' => $teacherA->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicleA->id,
                'type' => Appointment::TYPE_LESSON,
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ])
            ->assertRedirect();

        $this
            ->actingAs($user)
            ->post(route('appointments.store'), [
                'teacher_id' => $teacherB->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicleB->id,
                'type' => Appointment::TYPE_LESSON,
                'slot_date' => '2026-03-23',
                'slot_time' => '07:50',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'student_id' => $student->id,
            'vehicle_id' => $vehicleA->id,
            'lesson_category' => 'A',
        ]);
        $this->assertDatabaseHas('appointments', [
            'student_id' => $student->id,
            'vehicle_id' => $vehicleB->id,
            'lesson_category' => 'B',
        ]);
    }

    public function test_vehicle_schedule_hides_teacher_not_available_for_scheduling(): void
    {
        $user = User::factory()->create();
        Teacher::query()->create([
            'nome' => 'Professor Disponivel',
            'cpf' => '321.321.321-32',
            'telefone' => '(81) 90000-1000',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
            'status_agendamento' => Teacher::STATUS_AVAILABLE,
        ]);
        Teacher::query()->create([
            'nome' => 'Professor de Ferias',
            'cpf' => '654.654.654-65',
            'telefone' => '(81) 90000-2000',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
            'status_agendamento' => Teacher::STATUS_VACATION,
        ]);
        $vehicle = Vehicle::query()->create([
            'placa' => 'FER1A55',
            'categoria' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('appointments.index', ['vehicle' => $vehicle->id, 'week_start' => '2026-03-23']));

        $response->assertOk();
        $response->assertSee('Professor Disponivel');
        $response->assertDontSee('Professor de Ferias');
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
            ->from(route('appointments.index', ['vehicle' => $vehicle->id, 'week_start' => '2026-03-23']))
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_book_professor_outside_teacher_shift(): void
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
            ->from(route('appointments.index', ['vehicle' => $vehicle->id, 'week_start' => '2026-03-23']))
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
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
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ])
            ->assertRedirect();

        $response = $this
            ->actingAs($user)
            ->from(route('appointments.index', ['vehicle' => $vehicle->id, 'week_start' => '2026-03-23']))
            ->post(route('appointments.store'), [
                'teacher_id' => $secondTeacher->id,
                'student_id' => $secondStudent->id,
                'vehicle_id' => $vehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_book_same_teacher_in_two_vehicles_at_same_time(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor Compartilhado',
            'cpf' => '123.789.456-11',
            'telefone' => '(81) 97777-1000',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $firstVehicle = Vehicle::query()->create([
            'placa' => 'GGG7G77',
            'categoria' => 'B',
        ]);
        $secondVehicle = Vehicle::query()->create([
            'placa' => 'HHH8H88',
            'categoria' => 'B',
        ]);
        $firstStudent = Student::query()->create([
            'nome' => 'Aluno Um',
            'endereco' => 'Rua H',
            'telefone' => '(81) 98888-2000',
            'data_nascimento' => '2003-08-08',
            'cpf' => '741.741.741-11',
            'nome_mae' => 'Mae Um',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);
        $secondStudent = Student::query()->create([
            'nome' => 'Aluno Dois',
            'endereco' => 'Rua I',
            'telefone' => '(81) 98888-3000',
            'data_nascimento' => '2004-09-09',
            'cpf' => '852.852.852-22',
            'nome_mae' => 'Mae Dois',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        $this
            ->actingAs($user)
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $firstStudent->id,
                'vehicle_id' => $firstVehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ])
            ->assertRedirect();

        $response = $this
            ->actingAs($user)
            ->from(route('appointments.index', ['vehicle' => $secondVehicle->id, 'week_start' => '2026-03-23']))
            ->post(route('appointments.store'), [
                'teacher_id' => $teacher->id,
                'student_id' => $secondStudent->id,
                'vehicle_id' => $secondVehicle->id,
                'type' => Appointment::TYPE_LESSON,
                'slot_date' => '2026-03-23',
                'slot_time' => '07:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_vehicle_schedule_no_longer_shows_teacher_summary_section(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor Resumo',
            'cpf' => '444.333.222-11',
            'telefone' => '(81) 96666-4444',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
        ]);
        $vehicle = Vehicle::query()->create([
            'placa' => 'ZZZ9Z99',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Aluno Resumo',
            'endereco' => 'Rua Z',
            'telefone' => '(81) 91111-9999',
            'data_nascimento' => '2000-10-10',
            'cpf' => '963.258.741-55',
            'nome_mae' => 'Mae Resumo',
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
            ->get(route('appointments.index', ['vehicle' => $vehicle->id, 'week_start' => '2026-03-23']));

        $response->assertOk();
        $response->assertDontSee('Grade semanal de professores');
        $response->assertDontSee('Resumo por professor');
    }
}
