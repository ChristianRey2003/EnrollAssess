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
        // 1. Add columns to exams table for question bank quotas (only if they don't exist)
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'total_items')) {
                $table->integer('total_items')->nullable()->after('duration_minutes');
            }
            if (!Schema::hasColumn('exams', 'mcq_quota')) {
                $table->integer('mcq_quota')->nullable()->after('total_items');
            }
            if (!Schema::hasColumn('exams', 'tf_quota')) {
                $table->integer('tf_quota')->nullable()->after('mcq_quota');
            }
        });

        // 2. Make questions.exam_set_id nullable for question bank usage (only if it exists)
        if (Schema::hasColumn('questions', 'exam_set_id')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->foreignId('exam_set_id')->nullable()->change();
            });
        }

        // 3. Add correct_answer column for true/false questions (new style)
        if (!Schema::hasColumn('questions', 'correct_answer')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->boolean('correct_answer')->nullable()->after('question_type');
            });
        }

        // 4. Create exam_assignments table for per-student exam generation (only if it doesn't exist)
        if (!Schema::hasTable('exam_assignments')) {
            Schema::create('exam_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_id')->constrained('exams', 'exam_id')->onDelete('cascade');
                $table->foreignId('applicant_id')->constrained('applicants', 'applicant_id')->onDelete('cascade');
                $table->enum('status', ['pending', 'in_progress', 'completed', 'expired'])->default('pending');
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                // Ensure one assignment per applicant per exam
                $table->unique(['exam_id', 'applicant_id']);
            });
        }

        // 5. Create exam_assignment_questions table for persisted question selection (only if it doesn't exist)
        if (!Schema::hasTable('exam_assignment_questions')) {
            Schema::create('exam_assignment_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_assignment_id')->constrained('exam_assignments')->onDelete('cascade');
                $table->foreignId('question_id')->constrained('questions', 'question_id')->onDelete('cascade');
                $table->integer('position'); // Order in exam (1..N)
                $table->json('option_order')->nullable(); // Shuffled option IDs for MCQ
                $table->timestamps();

                // Index for efficient loading
                $table->index(['exam_assignment_id', 'position']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_assignment_questions');
        Schema::dropIfExists('exam_assignments');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('correct_answer');
        });

        // Restore exam_set_id to NOT NULL (careful with existing data)
        Schema::table('questions', function (Blueprint $table) {
            // Note: This will fail if there are questions with null exam_set_id
            // Manual intervention may be required in production
            $table->foreignId('exam_set_id')->nullable(false)->change();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['total_items', 'mcq_quota', 'tf_quota']);
        });
    }
};

