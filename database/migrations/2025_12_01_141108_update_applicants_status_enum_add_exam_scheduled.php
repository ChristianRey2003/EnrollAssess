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
            // Update the status enum to include exam scheduling statuses
            $table->enum('status', [
                'pending', 
                'exam-scheduled',      // NEW: Scheduled for exam
                'exam-completed', 
                'exam-no-show',         // NEW: Did not show up for scheduled exam
                'interview-available',  
                'interview-claimed',    
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
            // Revert to previous enum (without exam-scheduled and exam-no-show)
            $table->enum('status', [
                'pending', 
                'exam-completed', 
                'interview-available',  
                'interview-claimed',    
                'interview-scheduled', 
                'interview-completed', 
                'admitted', 
                'rejected'
            ])->default('pending')->change();
        });
    }
};
