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
        // Add missing indexes for production optimization
        // Note: Using try-catch to gracefully handle if indexes already exist
        
        // Applicants table - assigned_instructor_id index (missing from previous migration)
        if (Schema::hasTable('applicants') && Schema::hasColumn('applicants', 'assigned_instructor_id')) {
            try {
                Schema::table('applicants', function (Blueprint $table) {
                    // Index for assigned_instructor_id (used in joins and filtering)
                    $table->index('assigned_instructor_id', 'idx_applicants_assigned_instructor');
                });
            } catch (\Exception $e) {
                // Index may already exist, ignore
            }
            
            try {
                Schema::table('applicants', function (Blueprint $table) {
                    // Composite index for status + assigned_instructor_id (common query pattern)
                    $table->index(['status', 'assigned_instructor_id'], 'idx_applicants_status_instructor');
                });
            } catch (\Exception $e) {
                // Index may already exist, ignore
            }
        }

        // Access codes table - ensure is_used index exists (for filtering)
        if (Schema::hasTable('access_codes')) {
            try {
                Schema::table('access_codes', function (Blueprint $table) {
                    $table->index('is_used', 'idx_access_codes_is_used');
                });
            } catch (\Exception $e) {
                // Index may already exist, ignore
            }
        }

        // Results table - ensure composite index exists for applicant_id + question_id
        if (Schema::hasTable('results')) {
            try {
                Schema::table('results', function (Blueprint $table) {
                    $table->index(['applicant_id', 'question_id'], 'idx_results_applicant_question');
                });
            } catch (\Exception $e) {
                // Index may already exist, ignore
            }
        }

        // Interviews table - ensure interviewer_id index exists
        if (Schema::hasTable('interviews')) {
            try {
                Schema::table('interviews', function (Blueprint $table) {
                    $table->index('interviewer_id', 'idx_interviews_interviewer_id');
                });
            } catch (\Exception $e) {
                // Index may already exist, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('applicants') && Schema::hasColumn('applicants', 'assigned_instructor_id')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->dropIndex('idx_applicants_assigned_instructor');
                $table->dropIndex('idx_applicants_status_instructor');
            });
        }

        if (Schema::hasTable('access_codes')) {
            Schema::table('access_codes', function (Blueprint $table) {
                $table->dropIndex('idx_access_codes_is_used');
            });
        }

        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                $table->dropIndex('idx_results_applicant_question');
            });
        }

        if (Schema::hasTable('interviews')) {
            Schema::table('interviews', function (Blueprint $table) {
                $table->dropIndex('idx_interviews_interviewer_id');
            });
        }
    }
};