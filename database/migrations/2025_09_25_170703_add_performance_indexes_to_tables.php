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
        if (Schema::hasTable('applicants')) {
            $indexesNames = $this->getExistingIndexes('applicants');
            $hasStatus = Schema::hasColumn('applicants', 'status');
            $hasCreatedAt = Schema::hasColumn('applicants', 'created_at');
            $hasExamSetId = Schema::hasColumn('applicants', 'exam_set_id');
            $hasEmail = Schema::hasColumn('applicants', 'email_address');
            $hasApplicationNo = Schema::hasColumn('applicants', 'application_no');
            $hasScore = Schema::hasColumn('applicants', 'score');

            Schema::table('applicants', function (Blueprint $table) use (
                $indexesNames,
                $hasStatus,
                $hasCreatedAt,
                $hasExamSetId,
                $hasEmail,
                $hasApplicationNo,
                $hasScore
            ) {
                if ($hasStatus && $hasCreatedAt && !in_array('idx_applicants_status_created', $indexesNames)) {
                    $table->index(['status', 'created_at'], 'idx_applicants_status_created');
                }

                if ($hasExamSetId && $hasStatus && !in_array('idx_applicants_exam_set_status', $indexesNames)) {
                    $table->index(['exam_set_id', 'status'], 'idx_applicants_exam_set_status');
                }

                if ($hasEmail && !in_array('idx_applicants_email', $indexesNames)) {
                    $table->index('email_address', 'idx_applicants_email');
                }

                if ($hasApplicationNo && !in_array('idx_applicants_app_no', $indexesNames)) {
                    $table->index('application_no', 'idx_applicants_app_no');
                }

                if ($hasScore && $hasStatus && !in_array('idx_applicants_score_status', $indexesNames)) {
                    $table->index(['score', 'status'], 'idx_applicants_score_status');
                }
            });
        }

        // Interviews table indexes
        if (Schema::hasTable('interviews')) {
            $indexesNames = $this->getExistingIndexes('interviews');
            $hasApplicantId = Schema::hasColumn('interviews', 'applicant_id');
            $hasStatus = Schema::hasColumn('interviews', 'status');
            $hasInterviewerId = Schema::hasColumn('interviews', 'interviewer_id');
            $hasScheduleDate = Schema::hasColumn('interviews', 'schedule_date');

            Schema::table('interviews', function (Blueprint $table) use (
                $indexesNames,
                $hasApplicantId,
                $hasStatus,
                $hasInterviewerId,
                $hasScheduleDate
            ) {
                if ($hasApplicantId && $hasStatus && !in_array('idx_interviews_applicant_status', $indexesNames)) {
                    $table->index(['applicant_id', 'status'], 'idx_interviews_applicant_status');
                }

                if ($hasInterviewerId && $hasScheduleDate && !in_array('idx_interviews_interviewer_schedule', $indexesNames)) {
                    $table->index(['interviewer_id', 'schedule_date'], 'idx_interviews_interviewer_schedule');
                }

                if ($hasStatus && $hasScheduleDate && !in_array('idx_interviews_status_schedule', $indexesNames)) {
                    $table->index(['status', 'schedule_date'], 'idx_interviews_status_schedule');
                }
            });
        }

        // Results table indexes for exam analytics
        if (Schema::hasTable('results')) {
            $indexesNames = $this->getExistingIndexes('results');
            $hasApplicantId = Schema::hasColumn('results', 'applicant_id');
            $hasQuestionId = Schema::hasColumn('results', 'question_id');
            $hasIsCorrect = Schema::hasColumn('results', 'is_correct');
            $hasAnsweredAt = Schema::hasColumn('results', 'answered_at');

            Schema::table('results', function (Blueprint $table) use (
                $indexesNames,
                $hasApplicantId,
                $hasQuestionId,
                $hasIsCorrect,
                $hasAnsweredAt
            ) {
                if ($hasApplicantId && $hasIsCorrect && !in_array('idx_results_applicant_correct', $indexesNames)) {
                    $table->index(['applicant_id', 'is_correct'], 'idx_results_applicant_correct');
                }

                if ($hasQuestionId && $hasIsCorrect && !in_array('idx_results_question_correct', $indexesNames)) {
                    $table->index(['question_id', 'is_correct'], 'idx_results_question_correct');
                }

                if ($hasAnsweredAt && !in_array('idx_results_answered_at', $indexesNames)) {
                    $table->index('answered_at', 'idx_results_answered_at');
                }
            });
        }

        // Questions table indexes
        if (Schema::hasTable('questions')) {
            $indexesNames = $this->getExistingIndexes('questions');
            $hasExamSetId = Schema::hasColumn('questions', 'exam_set_id');
            $hasIsActive = Schema::hasColumn('questions', 'is_active');
            $hasOrderNumber = Schema::hasColumn('questions', 'order_number');
            $hasQuestionType = Schema::hasColumn('questions', 'question_type');

            Schema::table('questions', function (Blueprint $table) use (
                $indexesNames,
                $hasExamSetId,
                $hasIsActive,
                $hasOrderNumber,
                $hasQuestionType
            ) {
                if ($hasExamSetId && $hasIsActive && $hasOrderNumber && !in_array('idx_questions_exam_set_active_order', $indexesNames)) {
                    $table->index(['exam_set_id', 'is_active', 'order_number'], 'idx_questions_exam_set_active_order');
                }

                if ($hasQuestionType && $hasIsActive && !in_array('idx_questions_type_active', $indexesNames)) {
                    $table->index(['question_type', 'is_active'], 'idx_questions_type_active');
                }
            });
        }

        // Access codes table indexes
        if (Schema::hasTable('access_codes')) {
            $indexesNames = $this->getExistingIndexes('access_codes');
            $hasCode = Schema::hasColumn('access_codes', 'code');
            $hasIsUsed = Schema::hasColumn('access_codes', 'is_used');
            $hasExpiresAt = Schema::hasColumn('access_codes', 'expires_at');
            $hasApplicantId = Schema::hasColumn('access_codes', 'applicant_id');

            Schema::table('access_codes', function (Blueprint $table) use (
                $indexesNames,
                $hasCode,
                $hasIsUsed,
                $hasExpiresAt,
                $hasApplicantId
            ) {
                if ($hasCode && !in_array('idx_access_codes_code', $indexesNames)) {
                    $table->index('code', 'idx_access_codes_code');
                }

                if ($hasIsUsed && $hasExpiresAt && !in_array('idx_access_codes_used_expires', $indexesNames)) {
                    $table->index(['is_used', 'expires_at'], 'idx_access_codes_used_expires');
                }

                if ($hasApplicantId && !in_array('idx_access_codes_applicant', $indexesNames)) {
                    $table->index('applicant_id', 'idx_access_codes_applicant');
                }
            });
        }

        // Users table indexes
        if (Schema::hasTable('users')) {
            $indexesNames = $this->getExistingIndexes('users');
            $hasRole = Schema::hasColumn('users', 'role');
            $hasUsername = Schema::hasColumn('users', 'username');
            $hasUpdatedAt = Schema::hasColumn('users', 'updated_at');

            Schema::table('users', function (Blueprint $table) use (
                $indexesNames,
                $hasRole,
                $hasUsername,
                $hasUpdatedAt
            ) {
                if ($hasRole && !in_array('idx_users_role', $indexesNames)) {
                    $table->index('role', 'idx_users_role');
                }

                if ($hasUsername && !in_array('idx_users_username', $indexesNames)) {
                    $table->index('username', 'idx_users_username');
                }

                if ($hasUpdatedAt && !in_array('idx_users_updated_at', $indexesNames)) {
                    $table->index('updated_at', 'idx_users_updated_at');
                }
            });
        }

        // Exam sets table indexes
        if (Schema::hasTable('exam_sets')) {
            $indexesNames = $this->getExistingIndexes('exam_sets');
            $hasIsActive = Schema::hasColumn('exam_sets', 'is_active');
            $hasExamId = Schema::hasColumn('exam_sets', 'exam_id');

            Schema::table('exam_sets', function (Blueprint $table) use ($indexesNames, $hasIsActive, $hasExamId) {
                if ($hasIsActive && $hasExamId && !in_array('idx_exam_sets_active_exam', $indexesNames)) {
                    $table->index(['is_active', 'exam_id'], 'idx_exam_sets_active_exam');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('applicants')) {
            $indexesNames = $this->getExistingIndexes('applicants');

            Schema::table('applicants', function (Blueprint $table) use ($indexesNames) {
                if (in_array('idx_applicants_status_created', $indexesNames)) {
                    $table->dropIndex('idx_applicants_status_created');
                }
                if (in_array('idx_applicants_exam_set_status', $indexesNames)) {
                    $table->dropIndex('idx_applicants_exam_set_status');
                }
                if (in_array('idx_applicants_email', $indexesNames)) {
                    $table->dropIndex('idx_applicants_email');
                }
                if (in_array('idx_applicants_app_no', $indexesNames)) {
                    $table->dropIndex('idx_applicants_app_no');
                }
                if (in_array('idx_applicants_score_status', $indexesNames)) {
                    $table->dropIndex('idx_applicants_score_status');
                }
            });
        }

        if (Schema::hasTable('interviews')) {
            $indexesNames = $this->getExistingIndexes('interviews');

            Schema::table('interviews', function (Blueprint $table) use ($indexesNames) {
                if (in_array('idx_interviews_applicant_status', $indexesNames)) {
                    $table->dropIndex('idx_interviews_applicant_status');
                }
                if (in_array('idx_interviews_interviewer_schedule', $indexesNames)) {
                    $table->dropIndex('idx_interviews_interviewer_schedule');
                }
                if (in_array('idx_interviews_status_schedule', $indexesNames)) {
                    $table->dropIndex('idx_interviews_status_schedule');
                }
            });
        }

        if (Schema::hasTable('results')) {
            $indexesNames = $this->getExistingIndexes('results');

            Schema::table('results', function (Blueprint $table) use ($indexesNames) {
                if (in_array('idx_results_applicant_correct', $indexesNames)) {
                    $table->dropIndex('idx_results_applicant_correct');
                }
                if (in_array('idx_results_question_correct', $indexesNames)) {
                    $table->dropIndex('idx_results_question_correct');
                }
                if (in_array('idx_results_answered_at', $indexesNames)) {
                    $table->dropIndex('idx_results_answered_at');
                }
            });
        }

        if (Schema::hasTable('questions')) {
            $indexesNames = $this->getExistingIndexes('questions');

            Schema::table('questions', function (Blueprint $table) use ($indexesNames) {
                if (in_array('idx_questions_exam_set_active_order', $indexesNames)) {
                    $table->dropIndex('idx_questions_exam_set_active_order');
                }
                if (in_array('idx_questions_type_active', $indexesNames)) {
                    $table->dropIndex('idx_questions_type_active');
                }
            });
        }

        if (Schema::hasTable('access_codes')) {
            $indexesNames = $this->getExistingIndexes('access_codes');

            Schema::table('access_codes', function (Blueprint $table) use ($indexesNames) {
                if (in_array('idx_access_codes_code', $indexesNames)) {
                    $table->dropIndex('idx_access_codes_code');
                }
                if (in_array('idx_access_codes_used_expires', $indexesNames)) {
                    $table->dropIndex('idx_access_codes_used_expires');
                }
                if (in_array('idx_access_codes_applicant', $indexesNames)) {
                    $table->dropIndex('idx_access_codes_applicant');
                }
            });
        }

        if (Schema::hasTable('users')) {
            $indexesNames = $this->getExistingIndexes('users');

            Schema::table('users', function (Blueprint $table) use ($indexesNames) {
                if (in_array('idx_users_role', $indexesNames)) {
                    $table->dropIndex('idx_users_role');
                }
                if (in_array('idx_users_username', $indexesNames)) {
                    $table->dropIndex('idx_users_username');
                }
                if (in_array('idx_users_updated_at', $indexesNames)) {
                    $table->dropIndex('idx_users_updated_at');
                }
            });
        }

        if (Schema::hasTable('exam_sets')) {
            $indexesNames = $this->getExistingIndexes('exam_sets');

            Schema::table('exam_sets', function (Blueprint $table) use ($indexesNames) {
                if (in_array('idx_exam_sets_active_exam', $indexesNames)) {
                    $table->dropIndex('idx_exam_sets_active_exam');
                }
            });
        }
    }

    /**
     * Retrieve index names without relying on doctrine/dbal.
     */
    private function getExistingIndexes(string $table): array
    {
        $connection = Schema::getConnection();
        $tableName = $connection->getTablePrefix() . $table;
        $driver = $connection->getDriverName();

        $names = [];

        if (in_array($driver, ['mysql', 'mariadb'])) {
            $escapedTableName = str_replace('`', '``', $tableName);
            $rows = $connection->select("SHOW INDEX FROM `{$escapedTableName}`");

            foreach ($rows as $row) {
                if (is_object($row) && isset($row->Key_name)) {
                    $names[] = $row->Key_name;
                } elseif (is_array($row) && isset($row['Key_name'])) {
                    $names[] = $row['Key_name'];
                }
            }
        } elseif ($driver === 'sqlite') {
            $escapedTableName = str_replace("'", "''", $tableName);
            $rows = $connection->select("PRAGMA index_list('{$escapedTableName}')");

            foreach ($rows as $row) {
                if (is_object($row) && isset($row->name)) {
                    $names[] = $row->name;
                } elseif (is_array($row) && isset($row['name'])) {
                    $names[] = $row['name'];
                }
            }
        }

        return array_values(array_unique($names));
    }
};