<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure all individual name fields are populated from full_name
        $applicants = DB::table('applicants')->whereNotNull('full_name')->where('full_name', '!=', '')->get();
        
        foreach ($applicants as $applicant) {
            // Only update if individual names are not already set
            if (empty($applicant->first_name) && empty($applicant->last_name)) {
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

        // Now modify the table structure
        Schema::table('applicants', function (Blueprint $table) {
            // Make individual name fields required (not nullable)
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            
            // Remove the old full_name column
            $table->dropColumn('full_name');
            
            // Remove old columns that are not in the 10 stakeholder columns
            $table->dropColumn(['address', 'education_background']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Add back the dropped columns
            $table->string('full_name')->after('last_name');
            $table->text('address')->nullable()->after('phone_number');
            $table->string('education_background')->nullable()->after('address');
            
            // Make name fields nullable again
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
        });

        // Backfill full_name from individual components
        $applicants = DB::table('applicants')->get();
        
        foreach ($applicants as $applicant) {
            $fullName = trim(($applicant->first_name ?? '') . ' ' . ($applicant->middle_name ?? '') . ' ' . ($applicant->last_name ?? ''));
            $fullName = preg_replace('/\s+/', ' ', $fullName); // Remove extra spaces
            
            DB::table('applicants')
                ->where('applicant_id', $applicant->applicant_id)
                ->update(['full_name' => $fullName]);
        }
    }
};