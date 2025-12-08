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
        Schema::table('applicant_basic_infos', function (Blueprint $table) {
            $table->enum('senior_high_school_strand', ['ABM', 'STEM', 'HUMSS', 'TVL', 'Others'])->nullable()->change();
            $table->string('senior_high_school_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_basic_infos', function (Blueprint $table) {
            $table->enum('senior_high_school_strand', ['ABM', 'STEM', 'HUMSS', 'TVL', 'Others'])->nullable(false)->change();
            $table->string('senior_high_school_name')->nullable(false)->change();
        });
    }
};
