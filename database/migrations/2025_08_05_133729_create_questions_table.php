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
        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id'); // Primary key as per ERD
            $table->foreignId('exam_set_id')->constrained('exam_sets', 'exam_set_id')->onDelete('cascade');
            $table->text('question_text'); // as per ERD
            $table->enum('question_type', ['multiple_choice', 'true_false', 'essay'])->default('multiple_choice'); // as per ERD
            $table->integer('points')->default(1);
            $table->integer('order_number')->nullable(); // For question ordering
            $table->text('explanation')->nullable(); // Optional explanation for answers
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};