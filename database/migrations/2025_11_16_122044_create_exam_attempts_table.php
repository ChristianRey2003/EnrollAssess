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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id('attempt_id');
            $table->foreignId('applicant_id')->constrained('applicants', 'applicant_id')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams', 'exam_id')->onDelete('cascade');
            $table->string('attempt_token', 36)->unique(); // UUID for tracking
            $table->json('question_ids'); // Array of question IDs assigned to this attempt
            $table->timestamp('started_at');
            $table->integer('duration_minutes'); // Exam duration in minutes
            $table->json('answers')->nullable(); // Current answers: {question_id: answer}
            $table->integer('current_section')->default(0);
            $table->json('sections_completed')->nullable(); // Array of completed section types
            $table->enum('status', ['in_progress', 'completed', 'expired', 'abandoned'])->default('in_progress');
            $table->integer('violation_count')->default(0);
            $table->timestamp('last_activity_at')->nullable(); // Track last interaction
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['applicant_id', 'status']);
            $table->index(['exam_id', 'status']);
            $table->index('attempt_token');
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
