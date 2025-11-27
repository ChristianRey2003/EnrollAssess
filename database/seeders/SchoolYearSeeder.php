<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SchoolYear;
use App\Models\Applicant;
use Illuminate\Support\Facades\DB;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates only the current academic year and assigns all existing applicants to it.
     * Removes old academic years to keep only the current one.
     * Additional academic years can be added manually in settings.
     */
    public function run(): void
    {
        $currentDate = now();
        $currentYear = $currentDate->year;
        $currentMonth = $currentDate->month;
        
        // Determine the current academic year
        // Academic year typically runs from June to May
        // If we're in Jan-May, we're in the second half of the previous academic year
        // If we're in Jun-Dec, we're in the first half of the current academic year
        
        if ($currentMonth >= 6) {
            // June onwards - current academic year starts this year
            $academicYearStart = $currentYear;
            $academicYearEnd = $currentYear + 1;
        } else {
            // January to May - current academic year started last year
            $academicYearStart = $currentYear - 1;
            $academicYearEnd = $currentYear;
        }
        
        $currentAcademicYearName = "AY {$academicYearStart}-{$academicYearEnd}";
        
        // Get the first current school year (keep this one, delete duplicates)
        $keepSchoolYear = SchoolYear::where('name', $currentAcademicYearName)->first();
        
        // Reassign all applicants to the school year we're keeping (or will create)
        $schoolYearIdToKeep = null;
        
        if ($keepSchoolYear) {
            $schoolYearIdToKeep = $keepSchoolYear->school_year_id;
            // Delete duplicates of the current year
            $duplicatesDeleted = SchoolYear::where('name', $currentAcademicYearName)
                                          ->where('school_year_id', '!=', $schoolYearIdToKeep)
                                          ->delete();
            if ($duplicatesDeleted > 0) {
                $this->command->info("Removed {$duplicatesDeleted} duplicate(s) of {$currentAcademicYearName}.");
            }
            
            // Ensure it's marked as current
            if (!$keepSchoolYear->is_current) {
                $keepSchoolYear->setAsCurrent();
            }
            $schoolYear = $keepSchoolYear;
            $this->command->info("Using existing academic year: {$schoolYear->name}");
        } else {
            // Create the current academic year
            $startDate = \Carbon\Carbon::create($academicYearStart, 6, 1); // June 1
            $endDate = \Carbon\Carbon::create($academicYearEnd, 5, 31); // May 31
            
            $schoolYear = SchoolYear::create([
                'name' => $currentAcademicYearName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_current' => true,
                'is_active' => true,
            ]);
            
            $schoolYearIdToKeep = $schoolYear->school_year_id;
            $this->command->info("Created current academic year: {$currentAcademicYearName}");
        }
        
        // Delete all other school years (old years and any other duplicates)
        $deletedCount = SchoolYear::where('school_year_id', '!=', $schoolYearIdToKeep)->delete();
        if ($deletedCount > 0) {
            $this->command->info("Removed {$deletedCount} old/duplicate academic year(s).");
        }
        
        // Assign all existing applicants to this current school year
        $applicants = Applicant::whereNull('school_year_id')->get();
        $assignedCount = 0;
        
        foreach ($applicants as $applicant) {
            $applicant->update(['school_year_id' => $schoolYear->school_year_id]);
            $assignedCount++;
        }
        
        $this->command->info("Assigned {$assignedCount} existing applicant(s) to {$schoolYear->name}");
        $this->command->info('School year setup complete! Only current academic year is active. You can add more academic years manually in settings.');
    }
}
