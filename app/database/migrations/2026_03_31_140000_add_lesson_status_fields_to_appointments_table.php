<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('lesson_status', 30)->nullable()->after('notes');
            $table->text('lesson_status_notes')->nullable()->after('lesson_status');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['lesson_status', 'lesson_status_notes']);
        });
    }
};
