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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id('interview_id'); // Primary key as per ERD
            $table->foreignId('applicant_id')->constrained('applicants', 'applicant_id')->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->dateTime('schedule_date'); // as per ERD
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->integer('rating_communication')->nullable(); // 1-100 rating
            $table->integer('rating_technical')->nullable(); // 1-100 rating
            $table->integer('rating_problem_solving')->nullable(); // 1-100 rating
            $table->text('notes')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable(); // Calculated from ratings
            $table->enum('recommendation', ['recommended', 'waitlisted', 'not-recommended'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};