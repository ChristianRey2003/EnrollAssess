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
        Schema::table('applicants', function (Blueprint $table) {
            // Update the status enum to include all interview pool statuses
            $table->enum('status', [
                'pending', 
                'exam-completed', 
                'interview-available',  // Available in interview pool
                'interview-claimed',    // Claimed by instructor
                'interview-scheduled', 
                'interview-completed', 
                'admitted', 
                'rejected'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Revert to original enum
            $table->enum('status', [
                'pending', 
                'exam-completed', 
                'interview-scheduled', 
                'interview-completed', 
                'admitted', 
                'rejected'
            ])->default('pending')->change();
        });
    }
};