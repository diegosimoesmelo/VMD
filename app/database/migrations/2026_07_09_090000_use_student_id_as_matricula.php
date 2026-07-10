<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $students = DB::table('students')->select('id')->orderBy('id')->get();

        foreach ($students as $student) {
            DB::table('students')->where('id', $student->id)->update([
                'matricula' => 'tmp-'.$student->id,
            ]);
        }

        foreach ($students as $student) {
            DB::table('students')->where('id', $student->id)->update([
                'matricula' => (string) $student->id,
            ]);
        }
    }

    public function down(): void
    {
        $students = DB::table('students')
            ->select('id', 'created_at')
            ->orderBy('id')
            ->get();

        foreach ($students as $student) {
            DB::table('students')->where('id', $student->id)->update([
                'matricula' => 'tmp-'.$student->id,
            ]);
        }

        foreach ($students as $student) {
            $yearSuffix = $student->created_at !== null
                ? (int) Carbon::parse($student->created_at)->format('y')
                : (int) now()->format('y');

            DB::table('students')->where('id', $student->id)->update([
                'matricula' => sprintf('%02d%d', $yearSuffix, $student->id),
            ]);
        }
    }
};
