<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
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
            $table->string('matricula', 30)->nullable()->after('id');
        });

        $rows = DB::table('students')->select('id', 'created_at')->get();

        foreach ($rows as $row) {
            $yearSuffix = $row->created_at !== null
                ? (int) Carbon::parse($row->created_at)->format('y')
                : (int) now()->format('y');

            DB::table('students')->where('id', $row->id)->update([
                'matricula' => sprintf('%02d%d', $yearSuffix, $row->id),
            ]);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->unique('matricula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['matricula']);
            $table->dropColumn('matricula');
        });
    }
};
