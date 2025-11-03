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
            $table->enum('applicant_type', ['New College Applicant', 'Transferee', 'ALS passer'])->after('civil_status');
            $table->enum('is_pwd', ['Yes', 'No', 'Prefer not to answer'])->after('applicant_type');
            
            // Add index for applicant type for reporting/analytics
            $table->index('applicant_type');
            $table->index('is_pwd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_basic_infos', function (Blueprint $table) {
            $table->dropIndex(['applicant_type']);
            $table->dropIndex(['is_pwd']);
            $table->dropColumn(['applicant_type', 'is_pwd']);
        });
    }
};

