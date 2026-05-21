<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('nome', 'students_nome_idx');
            $table->index('teacher_id', 'students_teacher_id_idx');
            $table->index('status', 'students_status_idx');
            $table->index(['status', 'nome'], 'students_status_nome_idx');
            $table->index('categoria_pretendida', 'students_categoria_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index('starts_at', 'appointments_starts_at_idx');
            $table->index(['student_id', 'starts_at'], 'appointments_student_starts_idx');
            $table->index(['type', 'lesson_category', 'starts_at'], 'appointments_type_category_starts_idx');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->index('nome', 'teachers_nome_idx');
            $table->index(['status_agendamento', 'nome'], 'teachers_status_nome_idx');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('categoria', 'vehicles_categoria_idx');
            $table->index(['categoria', 'placa'], 'vehicles_categoria_placa_idx');
        });

        Schema::table('student_lesson_purchases', function (Blueprint $table) {
            $table->index(['student_id', 'purchased_at'], 'student_lesson_purchases_student_purchased_idx');
            $table->index(['student_id', 'lesson_category'], 'student_lesson_purchases_student_category_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'name'], 'users_role_name_idx');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS students_lower_nome_idx ON students (LOWER(nome))');
            DB::statement("CREATE INDEX IF NOT EXISTS students_cpf_digits_idx ON students ((regexp_replace(cpf, '\\D', '', 'g')))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS students_lower_nome_idx');
            DB::statement('DROP INDEX IF EXISTS students_cpf_digits_idx');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_name_idx');
        });

        Schema::table('student_lesson_purchases', function (Blueprint $table) {
            $table->dropIndex('student_lesson_purchases_student_purchased_idx');
            $table->dropIndex('student_lesson_purchases_student_category_idx');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_categoria_idx');
            $table->dropIndex('vehicles_categoria_placa_idx');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('teachers_nome_idx');
            $table->dropIndex('teachers_status_nome_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_starts_at_idx');
            $table->dropIndex('appointments_student_starts_idx');
            $table->dropIndex('appointments_type_category_starts_idx');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_nome_idx');
            $table->dropIndex('students_teacher_id_idx');
            $table->dropIndex('students_status_idx');
            $table->dropIndex('students_status_nome_idx');
            $table->dropIndex('students_categoria_idx');
        });
    }
};
