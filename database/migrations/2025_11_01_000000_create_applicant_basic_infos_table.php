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
        Schema::create('applicant_basic_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id')->unique();
            $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            
            // Personal Information
            $table->enum('sex', ['Male', 'Female', 'Other', 'Prefer not to say']);
            $table->date('date_of_birth');
            $table->integer('age');
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Separated', 'Divorced'])->nullable();
            
            // Address Information
            $table->text('complete_address');
            $table->string('city_municipality');
            $table->string('province');
            
            // Educational Background
            $table->enum('senior_high_school_strand', ['ABM', 'STEM', 'HUMSS', 'TVL', 'Others']);
            $table->string('senior_high_school_strand_other')->nullable();
            $table->string('senior_high_school_name');
            
            // Completion tracking
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('province');
            $table->index('senior_high_school_strand');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_basic_infos');
    }
};

