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
            $table->unsignedInteger('quantidade_aulas_a_contratadas')
                ->nullable()
                ->after('quantidade_aulas_restantes');
            $table->unsignedInteger('quantidade_aulas_a_restantes')
                ->nullable()
                ->after('quantidade_aulas_a_contratadas');
            $table->unsignedInteger('quantidade_aulas_b_contratadas')
                ->nullable()
                ->after('quantidade_aulas_a_restantes');
            $table->unsignedInteger('quantidade_aulas_b_restantes')
                ->nullable()
                ->after('quantidade_aulas_b_contratadas');
        });

        DB::table('students')
            ->where('categoria_pretendida', 'A')
            ->update([
                'quantidade_aulas_a_contratadas' => DB::raw('quantidade_aulas_contratadas'),
                'quantidade_aulas_a_restantes' => DB::raw('quantidade_aulas_restantes'),
            ]);

        DB::table('students')
            ->where('categoria_pretendida', 'B')
            ->update([
                'quantidade_aulas_b_contratadas' => DB::raw('quantidade_aulas_contratadas'),
                'quantidade_aulas_b_restantes' => DB::raw('quantidade_aulas_restantes'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'quantidade_aulas_a_contratadas',
                'quantidade_aulas_a_restantes',
                'quantidade_aulas_b_contratadas',
                'quantidade_aulas_b_restantes',
            ]);
        });
    }
};
