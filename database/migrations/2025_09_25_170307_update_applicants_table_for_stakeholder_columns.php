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
            // Add new columns for stakeholder requirements
            $table->string('preferred_course')->nullable()->after('education_background');
            $table->string('first_name')->nullable()->after('full_name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('verbal_description')->nullable()->after('score');
            
            // Add index for better performance on name searches
            $table->index(['last_name', 'first_name']);
            $table->index('preferred_course');
        });

        // Backfill name fields from existing full_name data (SQLite compatible)
        $applicants = DB::table('applicants')->whereNotNull('full_name')->where('full_name', '!=', '')->get();
        
        foreach ($applicants as $applicant) {
            $nameParts = explode(' ', trim($applicant->full_name));
            $firstName = $nameParts[0] ?? '';
            $lastName = count($nameParts) > 1 ? end($nameParts) : $firstName;
            $middleName = count($nameParts) > 2 ? $nameParts[1] : null;
            
            DB::table('applicants')
                ->where('applicant_id', $applicant->applicant_id)
                ->update([
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['last_name', 'first_name']);
            $table->dropIndex(['preferred_course']);
            
            // Drop columns
            $table->dropColumn([
                'preferred_course',
                'first_name',
                'middle_name', 
                'last_name',
                'verbal_description'
            ]);
        });
    }
};