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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id('applicant_id'); // Primary key as per ERD
            $table->string('application_no')->unique(); // ApplicationNo as per ERD
            $table->string('full_name');
            $table->string('email_address')->unique();
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('education_background')->nullable();
            // exam_set_id removed - now using direct instructor assignment
            $table->decimal('score', 5, 2)->nullable(); // Exam score
            $table->enum('status', ['pending', 'exam-completed', 'interview-scheduled', 'interview-completed', 'admitted', 'rejected'])->default('pending');
            $table->timestamp('exam_completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};