<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Vehicle;
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

    public function test_authenticated_user_is_saved_as_initial_registration_operator(): void
    {
        $user = User::factory()->create(['name' => 'Operador Cadastro']);

        $response = $this
            ->actingAs($user)
            ->post(route('students.store'), [
                'nome' => 'Aluno Venda Inicial',
                'endereco' => 'Rua da Matricula',
                'telefone' => '(81) 99999-0000',
                'data_nascimento' => '2000-05-10',
                'cpf' => '123.123.123-99',
                'nome_mae' => 'Mae Venda',
                'status' => Student::STATUS_THEORY_CLASS,
                'servico_oferecido' => 'primeira_habilitacao',
                'categoria_pretendida' => 'B',
                'valor_pago' => 1200,
                'payment_method' => 'pix',
                'quantidade_aulas_b_contratadas' => 20,
            ]);

        $response->assertRedirect(route('students.index'));

        $student = Student::query()->where('cpf', '123.123.123-99')->firstOrFail();

        $this->assertSame((string) $student->id, $student->matricula);
        $this->assertDatabaseHas('students', [
            'cpf' => '123.123.123-99',
            'operator_user_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_mark_and_filter_training_students(): void
    {
        $user = User::factory()->create();
        Student::query()->create([
            'nome' => 'Aluno Regular',
            'endereco' => 'Rua Regular',
            'telefone' => '(81) 98888-1111',
            'data_nascimento' => '2000-01-01',
            'cpf' => '111.111.111-99',
            'nome_mae' => 'Mae Regular',
            'status' => Student::STATUS_PRACTICAL_CLASS,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('students.store'), [
                'nome' => 'Aluno Treinamento',
                'endereco' => 'Rua Treinamento',
                'telefone' => '(81) 97777-2222',
                'data_nascimento' => '1998-04-12',
                'cpf' => '222.222.222-99',
                'nome_mae' => 'Mae Treinamento',
                'status' => Student::STATUS_PRACTICAL_CLASS,
                'servico_oferecido' => 'aula_habilitado',
                'categoria_pretendida' => 'B',
                'treinamento_para_habilitados' => '1',
            ]);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', [
            'cpf' => '222.222.222-99',
            'treinamento_para_habilitados' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('students.index', ['tab' => 'training']));

        $response->assertOk();
        $response->assertSee('Aluno Treinamento');
        $response->assertSee('Treinamento para Habilitados');
        $response->assertDontSee('Aluno Regular');
    }

    public function test_authenticated_user_is_saved_as_lesson_purchase_operator(): void
    {
        $user = User::factory()->create(['name' => 'Operador Compra']);
        $student = Student::query()->create([
            'nome' => 'Aluno Compra',
            'endereco' => 'Rua Compra',
            'telefone' => '(81) 98888-0000',
            'data_nascimento' => '2001-02-03',
            'cpf' => '987.987.987-99',
            'nome_mae' => 'Mae Compra',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('students.lesson-purchases.store', $student), [
                'lesson_category' => 'B',
                'quantity' => 5,
                'amount_paid' => 350,
                'payment_method' => 'pix',
            ]);

        $response->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('student_lesson_purchases', [
            'student_id' => $student->id,
            'user_id' => $user->id,
            'lesson_category' => 'B',
            'quantity' => 5,
        ]);
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

    public function test_authenticated_user_can_see_student_appointments_in_list_modal(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor Agenda',
            'cpf' => '123.123.123-12',
            'telefone' => '(81) 98888-7777',
            'categorias_ensino' => ['B'],
            'turnos_disponiveis' => ['manha'],
            'status_agendamento' => Teacher::STATUS_AVAILABLE,
        ]);
        $vehicle = Vehicle::query()->create([
            'placa' => 'QWE1R23',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Aluno Agenda',
            'endereco' => 'Rua Modal',
            'telefone' => '(81) 97777-1111',
            'data_nascimento' => '2001-01-01',
            'cpf' => '111.222.333-44',
            'nome_mae' => 'Mae Agenda',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'B',
        ]);

        Appointment::query()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'vehicle_id' => $vehicle->id,
            'type' => Appointment::TYPE_LESSON,
            'lesson_category' => 'B',
            'starts_at' => '2026-03-26 14:00:00',
            'ends_at' => '2026-03-26 14:50:00',
            'notes' => 'Aula de baliza',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('students.index'));

        $response->assertOk();
        $response->assertSee('Ver aulas');
        $response->assertSee('26/03/2026 as 14:00');
        $response->assertSee('Professor: Professor Agenda');
        $response->assertSee('Veiculo: QWE1R23');
        $response->assertSee('Observacoes: Aula de baliza');
    }

    public function test_student_lessons_pdf_uses_document_row_numbers_and_category_totals(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::query()->create([
            'nome' => 'Professor PDF',
            'cpf' => '321.321.321-12',
            'telefone' => '(81) 98888-1234',
            'categorias_ensino' => ['A', 'B'],
            'turnos_disponiveis' => ['manha'],
            'status_agendamento' => Teacher::STATUS_AVAILABLE,
        ]);
        $vehicleA = Vehicle::query()->create([
            'placa' => 'ABC1A23',
            'categoria' => 'A',
        ]);
        $vehicleB = Vehicle::query()->create([
            'placa' => 'DEF4B56',
            'categoria' => 'B',
        ]);
        $student = Student::query()->create([
            'nome' => 'Aluno PDF',
            'endereco' => 'Rua PDF',
            'telefone' => '(81) 97777-4444',
            'data_nascimento' => '2001-01-01',
            'cpf' => '222.333.444-55',
            'nome_mae' => 'Mae PDF',
            'status' => Student::STATUS_PRACTICAL_CLASS,
            'categoria_pretendida' => 'AB',
        ]);

        Appointment::query()->create([
            'teacher_id' => $teacher->id,
            'student_id' => null,
            'vehicle_id' => $vehicleB->id,
            'type' => Appointment::TYPE_UNAVAILABLE,
            'starts_at' => '2026-03-25 07:00:00',
            'ends_at' => '2026-03-25 07:50:00',
        ]);
        Appointment::query()->create([
            'teacher_id' => $teacher->id,
            'student_id' => null,
            'vehicle_id' => $vehicleB->id,
            'type' => Appointment::TYPE_UNAVAILABLE,
            'starts_at' => '2026-03-25 08:00:00',
            'ends_at' => '2026-03-25 08:50:00',
        ]);
        $lessonA = Appointment::query()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'vehicle_id' => $vehicleA->id,
            'type' => Appointment::TYPE_LESSON,
            'lesson_category' => 'A',
            'starts_at' => '2026-03-26 08:00:00',
            'ends_at' => '2026-03-26 08:50:00',
        ]);
        $lessonB = Appointment::query()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'vehicle_id' => $vehicleB->id,
            'type' => Appointment::TYPE_LESSON,
            'lesson_category' => 'B',
            'starts_at' => '2026-03-27 09:00:00',
            'ends_at' => '2026-03-27 09:50:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('students.lessons.pdf', [$student, 'category' => 'AB']));

        $response->assertOk();
        $pdf = $response->getContent();

        $this->assertStringContainsString('(1) Tj', $pdf);
        $this->assertStringContainsString('(2) Tj', $pdf);
        $this->assertStringNotContainsString('('.$lessonA->id.') Tj', $pdf);
        $this->assertStringNotContainsString('('.$lessonB->id.') Tj', $pdf);
        $this->assertStringContainsString('(Total de aulas: 2 por categoria) Tj', $pdf);
        $this->assertStringContainsString('(Categoria A: 1 aula) Tj', $pdf);
        $this->assertStringContainsString('(Categoria B: 1 aula) Tj', $pdf);
    }
}
