<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('status_agendamento', 30)->default('disponivel')->after('turnos_disponiveis');
        });

        DB::table('teachers')->update(['status_agendamento' => 'disponivel']);
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('status_agendamento');
        });
    }
};
