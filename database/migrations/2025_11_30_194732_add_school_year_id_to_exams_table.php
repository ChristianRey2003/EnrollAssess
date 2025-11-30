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
        Schema::table('exams', function (Blueprint $table) {
            $table->unsignedBigInteger('school_year_id')->nullable()->after('exam_id');
            $table->foreign('school_year_id')
                  ->references('school_year_id')
                  ->on('school_years')
                  ->onDelete('cascade');
            $table->index('school_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropIndex(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
