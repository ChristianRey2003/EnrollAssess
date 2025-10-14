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
        Schema::table('access_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('exam_id')->nullable()->after('applicant_id');
            $table->foreign('exam_id')->references('exam_id')->on('exams')->onDelete('cascade');
            $table->index('exam_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_codes', function (Blueprint $table) {
            $table->dropForeign(['exam_id']);
            $table->dropIndex(['exam_id']);
            $table->dropColumn('exam_id');
        });
    }
};
