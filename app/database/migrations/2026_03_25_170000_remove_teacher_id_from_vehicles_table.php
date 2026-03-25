<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::create('vehicles_tmp', function (Blueprint $table) {
                $table->id();
                $table->string('placa', 10)->unique();
                $table->string('categoria', 1);
                $table->timestamps();
            });

            DB::table('vehicles_tmp')->insertUsing(
                ['id', 'placa', 'categoria', 'created_at', 'updated_at'],
                DB::table('vehicles')->select('id', 'placa', 'categoria', 'created_at', 'updated_at')
            );

            Schema::drop('vehicles');
            Schema::rename('vehicles_tmp', 'vehicles');

            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::create('vehicles_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->string('placa', 10)->unique();
                $table->string('categoria', 1);
                $table->timestamps();
            });

            DB::table('vehicles_tmp')->insertUsing(
                ['id', 'placa', 'categoria', 'created_at', 'updated_at'],
                DB::table('vehicles')->select('id', 'placa', 'categoria', 'created_at', 'updated_at')
            );

            Schema::drop('vehicles');
            Schema::rename('vehicles_tmp', 'vehicles');

            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('id')->constrained('teachers')->nullOnDelete();
        });
    }
};
