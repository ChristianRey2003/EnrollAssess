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
            // Modify the status enum to include 'interview-available'
            $table->enum('status', [
                'pending', 
                'exam-completed', 
                'interview-available',  // NEW: Added for interview pool system
                'interview-claimed',    // NEW: Added for claimed interviews
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
            // Remove 'interview-available' from the enum
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