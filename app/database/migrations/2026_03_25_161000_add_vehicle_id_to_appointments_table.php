<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('student_id')->constrained('vehicles')->nullOnDelete();
            $table->unique(['vehicle_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique(['vehicle_id', 'starts_at']);
            $table->dropConstrainedForeignId('vehicle_id');
        });
    }
};
