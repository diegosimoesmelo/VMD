<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('id')->constrained('teachers')->nullOnDelete();
            $table->unique('teacher_id');
        });

        $teachers = DB::table('teachers')->get();

        foreach ($teachers as $teacher) {
            $hasUser = DB::table('users')->where('teacher_id', $teacher->id)->exists();

            if ($hasUser) {
                continue;
            }

            $username = preg_replace('/\D+/', '', (string) $teacher->cpf) ?: 'professor'.$teacher->id;

            DB::table('users')->insert([
                'teacher_id' => $teacher->id,
                'name' => $teacher->nome,
                'username' => $username,
                'role' => 'professor',
                'password' => Hash::make('vmdcfc'),
                'must_change_password' => true,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['teacher_id']);
            $table->dropConstrainedForeignId('teacher_id');
        });
    }
};
