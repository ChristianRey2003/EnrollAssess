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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id('option_id'); // Primary key as per ERD
            $table->foreignId('question_id')->constrained('questions', 'question_id')->onDelete('cascade');
            $table->text('option_text'); // as per ERD
            $table->boolean('is_correct')->default(false); // as per ERD
            $table->integer('order_number')->nullable(); // For option ordering (A, B, C, D)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};