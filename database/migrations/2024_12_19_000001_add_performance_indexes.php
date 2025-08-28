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
        // Applicants table indexes for better query performance
        Schema::table('applicants', function (Blueprint $table) {
            // Composite index for status-based filtering with pagination
            $table->index(['status', 'created_at'], 'idx_applicants_status_created');
            
            // Index for exam set filtering
            $table->index(['exam_set_id', 'status'], 'idx_applicants_exam_set_status');
            
            // Index for email lookups
            $table->index('email_address', 'idx_applicants_email');
            
            // Index for application number lookups
            $table->index('application_no', 'idx_applicants_app_no');
            
            // Index for score-based queries
            $table->index(['score', 'status'], 'idx_applicants_score_status');
        });

        // Interviews table indexes
        Schema::table('interviews', function (Blueprint $table) {
            // Composite index for applicant-interview lookups
            $table->index(['applicant_id', 'status'], 'idx_interviews_applicant_status');
            
            // Index for interviewer assignments
            $table->index(['interviewer_id', 'schedule_date'], 'idx_interviews_interviewer_schedule');
            
            // Index for status-based filtering
            $table->index(['status', 'schedule_date'], 'idx_interviews_status_schedule');
        });

        // Results table indexes for exam analytics
        Schema::table('results', function (Blueprint $table) {
            // Composite index for applicant results
            $table->index(['applicant_id', 'is_correct'], 'idx_results_applicant_correct');
            
            // Index for question analytics
            $table->index(['question_id', 'is_correct'], 'idx_results_question_correct');
            
            // Index for answered_at for time-based queries
            $table->index('answered_at', 'idx_results_answered_at');
        });

        // Questions table indexes
        Schema::table('questions', function (Blueprint $table) {
            // Composite index for exam set questions
            $table->index(['exam_set_id', 'is_active', 'order_number'], 'idx_questions_exam_set_active_order');
            
            // Index for question type filtering
            $table->index(['question_type', 'is_active'], 'idx_questions_type_active');
        });

        // Access codes table indexes
        Schema::table('access_codes', function (Blueprint $table) {
            // Index for code lookups
            $table->index('code', 'idx_access_codes_code');
            
            // Index for usage status
            $table->index(['is_used', 'expires_at'], 'idx_access_codes_used_expires');
            
            // Index for applicant lookups
            $table->index('applicant_id', 'idx_access_codes_applicant');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Index for role-based queries
            $table->index('role', 'idx_users_role');
            
            // Index for username lookups
            $table->index('username', 'idx_users_username');
            
            // Index for active user queries (based on updated_at)
            $table->index('updated_at', 'idx_users_updated_at');
        });

        // Exam sets table indexes
        Schema::table('exam_sets', function (Blueprint $table) {
            // Index for active exam sets
            $table->index(['is_active', 'exam_id'], 'idx_exam_sets_active_exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndex('idx_applicants_status_created');
            $table->dropIndex('idx_applicants_exam_set_status');
            $table->dropIndex('idx_applicants_email');
            $table->dropIndex('idx_applicants_app_no');
            $table->dropIndex('idx_applicants_score_status');
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->dropIndex('idx_interviews_applicant_status');
            $table->dropIndex('idx_interviews_interviewer_schedule');
            $table->dropIndex('idx_interviews_status_schedule');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('idx_results_applicant_correct');
            $table->dropIndex('idx_results_question_correct');
            $table->dropIndex('idx_results_answered_at');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('idx_questions_exam_set_active_order');
            $table->dropIndex('idx_questions_type_active');
        });

        Schema::table('access_codes', function (Blueprint $table) {
            $table->dropIndex('idx_access_codes_code');
            $table->dropIndex('idx_access_codes_used_expires');
            $table->dropIndex('idx_access_codes_applicant');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_username');
            $table->dropIndex('idx_users_updated_at');
        });

        Schema::table('exam_sets', function (Blueprint $table) {
            $table->dropIndex('idx_exam_sets_active_exam');
        });
    }
};
