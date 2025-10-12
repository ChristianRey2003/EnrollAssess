<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration refactors the system:
     * 1. Removes exam_sets - questions now belong directly to exams
     * 2. Removes interview pool - applicants assigned directly to instructors
     */
    public function up(): void
    {
        // 1. Add exam_id to questions table (questions now belong to exam, not exam_set)
        if (!Schema::hasColumn('questions', 'exam_id')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->foreignId('exam_id')->nullable()->after('question_id')->constrained('exams', 'exam_id')->onDelete('cascade');
            });

            // 2. Migrate data from exam_sets to exams
            // For each question, set exam_id based on its exam_set's exam_id
            if (Schema::hasTable('exam_sets')) {
                \DB::statement('
                    UPDATE questions q
                    INNER JOIN exam_sets es ON q.exam_set_id = es.exam_set_id
                    SET q.exam_id = es.exam_id
                    WHERE q.exam_set_id IS NOT NULL
                ');
            }

            // 3. Make exam_id NOT NULL now that data is migrated
            Schema::table('questions', function (Blueprint $table) {
                $table->foreignId('exam_id')->nullable(false)->change();
            });
        }

        // 4. Drop the old exam_set_id foreign key and column from questions
        if (Schema::hasColumn('questions', 'exam_set_id')) {
            // Check if foreign key exists
            $foreignKeyExists = \DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'questions' 
                AND CONSTRAINT_NAME = 'questions_exam_set_id_foreign'
            ");
            
            if (!empty($foreignKeyExists)) {
                Schema::table('questions', function (Blueprint $table) {
                    $table->dropForeign(['exam_set_id']);
                });
            }
            
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('exam_set_id');
            });
        }

        // 5. Add assigned_instructor_id to applicants table for direct instructor assignment
        if (!Schema::hasColumn('applicants', 'assigned_instructor_id')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->foreignId('assigned_instructor_id')->nullable()->after('exam_set_id')->constrained('users', 'user_id')->onDelete('set null');
            });
        }

        // 6. Drop exam_set_id from applicants (no longer needed)
        if (Schema::hasColumn('applicants', 'exam_set_id')) {
            // Check if foreign key exists
            $foreignKeyExists = \DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'applicants' 
                AND CONSTRAINT_NAME = 'applicants_exam_set_id_foreign'
            ");
            
            if (!empty($foreignKeyExists)) {
                Schema::table('applicants', function (Blueprint $table) {
                    $table->dropForeign(['exam_set_id']);
                });
            }
            
            Schema::table('applicants', function (Blueprint $table) {
                $table->dropColumn('exam_set_id');
            });
        }

        // 7. Remove interview pool columns from interviews table
        // First, drop foreign key on claimed_by if it exists
        $claimedByFkExists = \DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'interviews' 
            AND CONSTRAINT_NAME = 'interviews_claimed_by_foreign'
        ");
        
        if (!empty($claimedByFkExists)) {
            Schema::table('interviews', function (Blueprint $table) {
                $table->dropForeign(['claimed_by']);
            });
        }
        
        Schema::table('interviews', function (Blueprint $table) {
            // Drop pool-related columns only if they exist
            $columnsToCheck = ['pool_status', 'claimed_by', 'claimed_at', 'priority_level', 'dh_override', 'assignment_notes'];
            $columnsToRemove = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('interviews', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
            
            // Update status enum to remove pool-related statuses
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])
                ->default('scheduled')
                ->change();
                
            // Make interviewer_id NOT nullable (always assigned by admin)
            $table->foreignId('interviewer_id')->nullable(false)->change();
        });

        // 8. Drop exam_sets table (no longer needed)
        Schema::dropIfExists('exam_sets');

        // 9. Drop exam_assignments tables (replacing with simpler random selection)
        Schema::dropIfExists('exam_assignment_questions');
        Schema::dropIfExists('exam_assignments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recreate exam_sets table
        Schema::create('exam_sets', function (Blueprint $table) {
            $table->id('exam_set_id');
            $table->foreignId('exam_id')->constrained('exams', 'exam_id')->onDelete('cascade');
            $table->string('set_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Recreate exam_assignments tables
        Schema::create('exam_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams', 'exam_id')->onDelete('cascade');
            $table->foreignId('applicant_id')->constrained('applicants', 'applicant_id')->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'expired'])->default('pending');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'applicant_id']);
        });

        Schema::create('exam_assignment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_assignment_id')->constrained('exam_assignments')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions', 'question_id')->onDelete('cascade');
            $table->integer('position');
            $table->json('option_order')->nullable();
            $table->timestamps();
            $table->index(['exam_assignment_id', 'position']);
        });

        // 3. Add exam_set_id back to applicants
        Schema::table('applicants', function (Blueprint $table) {
            $table->foreignId('exam_set_id')->nullable()->after('phone_number')->constrained('exam_sets', 'exam_set_id')->onDelete('set null');
            $table->dropForeign(['assigned_instructor_id']);
            $table->dropColumn('assigned_instructor_id');
        });

        // 4. Restore interview pool columns
        Schema::table('interviews', function (Blueprint $table) {
            $table->enum('pool_status', ['available', 'claimed', 'assigned', 'completed'])->default('available')->after('status');
            $table->foreignId('claimed_by')->nullable()->constrained('users', 'user_id')->onDelete('set null')->after('interviewer_id');
            $table->timestamp('claimed_at')->nullable()->after('claimed_by');
            $table->enum('priority_level', ['high', 'medium', 'low'])->default('medium')->after('claimed_at');
            $table->boolean('dh_override')->default(false)->after('priority_level');
            $table->text('assignment_notes')->nullable()->after('dh_override');
            
            $table->enum('status', ['available', 'claimed', 'assigned', 'scheduled', 'completed', 'cancelled', 'rescheduled'])
                ->default('available')
                ->change();
                
            $table->foreignId('interviewer_id')->nullable()->change();
        });

        // 5. Add exam_set_id back to questions
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('exam_set_id')->nullable()->after('question_id')->constrained('exam_sets', 'exam_set_id')->onDelete('cascade');
        });

        // 6. Drop exam_id from questions
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['exam_id']);
            $table->dropColumn('exam_id');
        });
    }
};

