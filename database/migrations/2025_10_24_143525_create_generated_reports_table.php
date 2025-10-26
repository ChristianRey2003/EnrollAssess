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
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 100); // e.g., 'final_ranking', 'statistical_analysis', etc.
            $table->string('title', 255);
            $table->string('file_path');
            $table->json('filters_applied')->nullable(); // Store applied filters
            $table->unsignedBigInteger('generated_by'); // user_id of who generated the report
            $table->unsignedBigInteger('file_size')->nullable(); // File size in bytes
            $table->string('status', 50)->default('completed'); // completed, failed, processing
            $table->json('metadata')->nullable(); // Additional metadata like record count, etc.
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('generated_by')->references('user_id')->on('users')->onDelete('cascade');
            
            // Indexes for faster queries
            $table->index('report_type');
            $table->index('generated_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
