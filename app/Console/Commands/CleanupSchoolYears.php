<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SchoolYear;
use App\Models\Applicant;
use Carbon\Carbon;

class CleanupSchoolYears extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'school-year:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up school years - keep only current academic year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = now();
        $currentYear = $currentDate->year;
        $currentMonth = $currentDate->month;
        
        // Determine the current academic year
        if ($currentMonth >= 6) {
            $academicYearStart = $currentYear;
            $academicYearEnd = $currentYear + 1;
        } else {
            $academicYearStart = $currentYear - 1;
            $academicYearEnd = $currentYear;
        }
        
        $currentAcademicYearName = "AY {$academicYearStart}-{$academicYearEnd}";
        
        // Get the first current school year (keep this one)
        $keepSchoolYear = SchoolYear::where('name', $currentAcademicYearName)->first();
        
        if (!$keepSchoolYear) {
            // Create it if it doesn't exist
            $startDate = Carbon::create($academicYearStart, 6, 1);
            $endDate = Carbon::create($academicYearEnd, 5, 31);
            
            $keepSchoolYear = SchoolYear::create([
                'name' => $currentAcademicYearName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_current' => true,
                'is_active' => true,
            ]);
            
            $this->info("Created: {$currentAcademicYearName}");
        }
        
        // Reassign all applicants to the kept school year
        Applicant::where('school_year_id', '!=', $keepSchoolYear->school_year_id)
                 ->orWhereNull('school_year_id')
                 ->update(['school_year_id' => $keepSchoolYear->school_year_id]);
        
        // Delete all other school years
        $deletedCount = SchoolYear::where('school_year_id', '!=', $keepSchoolYear->school_year_id)->delete();
        
        // Ensure the kept one is marked as current
        $keepSchoolYear->setAsCurrent();
        
        $this->info("Removed {$deletedCount} old/duplicate school year(s).");
        $this->info("Kept: {$keepSchoolYear->name}");
        $this->info("All applicants assigned to: {$keepSchoolYear->name}");
        
        return 0;
    }
}
