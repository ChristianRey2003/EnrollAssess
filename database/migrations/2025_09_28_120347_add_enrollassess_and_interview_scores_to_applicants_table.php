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
        Schema::table('applicants', function (Blueprint $table) {
            // Add EnrollAssess internal exam scores
            $table->decimal('enrollassess_score', 5, 2)->nullable()->after('score')->comment('EnrollAssess internal exam scores');
            
            // Add interview evaluation scores
            $table->decimal('interview_score', 5, 2)->nullable()->after('enrollassess_score')->comment('Interview evaluation scores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['enrollassess_score', 'interview_score']);
        });
    }
};
