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
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id('exam_schedule_id');
            $table->foreignId('applicant_id')->constrained('applicants', 'applicant_id')->onDelete('cascade');
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->string('venue')->nullable();
            $table->text('special_instructions')->nullable();
            $table->foreignId('scheduled_by')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('status', ['scheduled', 'cancelled', 'completed', 'no-show'])->default('scheduled');
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->integer('reschedule_count')->default(0);
            $table->foreignId('previous_schedule_id')->nullable()->constrained('exam_schedules', 'exam_schedule_id')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('applicant_id');
            $table->index('scheduled_date');
            $table->index('status');
            $table->index(['scheduled_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
