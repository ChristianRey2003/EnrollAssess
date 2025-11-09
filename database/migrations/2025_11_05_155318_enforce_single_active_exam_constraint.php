<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure only one exam is active (set most recent active exam)
        DB::statement('UPDATE exams SET is_active = 0');
        $latestExam = DB::table('exams')->orderBy('created_at', 'desc')->first();
        if ($latestExam) {
            DB::table('exams')->where('exam_id', $latestExam->exam_id)->update(['is_active' => 1]);
        }
        
        // MySQL workaround: Add a virtual column that is NULL when inactive, 1 when active
        // Then create a unique index on that column (NULL values are ignored in unique indexes)
        Schema::table('exams', function (Blueprint $table) {
            $table->integer('active_flag')->nullable()->storedAs('CASE WHEN is_active = 1 THEN 1 ELSE NULL END')->after('is_active');
            $table->unique('active_flag', 'unique_active_exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropUnique('unique_active_exam');
            $table->dropColumn('active_flag');
        });
    }
};
