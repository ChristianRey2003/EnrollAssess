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
        Schema::create('results', function (Blueprint $table) {
            $table->id('result_id'); // Primary key as per ERD
            $table->foreignId('applicant_id')->constrained('applicants', 'applicant_id')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions', 'question_id')->onDelete('cascade');
            $table->text('answer_text')->nullable(); // For essay type questions
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options', 'option_id')->onDelete('set null'); // For multiple choice
            $table->boolean('is_correct')->nullable(); // Calculated field
            $table->decimal('points_earned', 5, 2)->default(0);
            $table->timestamp('answered_at');
            $table->timestamps();

            // Composite index for performance
            $table->index(['applicant_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};