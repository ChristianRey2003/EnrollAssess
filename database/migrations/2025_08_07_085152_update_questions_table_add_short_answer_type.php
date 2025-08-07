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
        Schema::table('questions', function (Blueprint $table) {
            // Modify the enum to include 'short_answer'
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer', 'essay'])
                  ->default('multiple_choice')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Revert back to the original enum values
            $table->enum('question_type', ['multiple_choice', 'true_false', 'essay'])
                  ->default('multiple_choice')
                  ->change();
        });
    }
};
