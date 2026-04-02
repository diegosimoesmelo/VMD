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
            $table->unsignedInteger('quantidade_aulas_restantes')
                ->nullable()
                ->after('quantidade_aulas_contratadas');
        });

        DB::table('students')
            ->whereNotNull('quantidade_aulas_contratadas')
            ->update([
                'quantidade_aulas_restantes' => DB::raw('quantidade_aulas_contratadas'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('quantidade_aulas_restantes');
        });
    }
};
