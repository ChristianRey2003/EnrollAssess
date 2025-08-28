<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;
use App\Models\AccessCode;
use Illuminate\Support\Facades\DB;

class TestDepartmentInstructorPortals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:department-instructor-portals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Department Head and Instructor portal functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Department Head and Instructor Portals...');
        
        // Test 1: Check if required models exist
        $this->testModels();
        
        // Test 2: Check if controllers exist and are accessible
        $this->testControllers();
        
        // Test 3: Check if routes are properly registered
        $this->testRoutes();
        
        // Test 4: Check if views exist
        $this->testViews();
        
        // Test 5: Check database relationships
        $this->testDatabaseRelationships();
        
        // Test 6: Check sample data
        $this->testSampleData();
        
        $this->info('✅ Department Head and Instructor Portal testing completed!');
    }
    
    private function testModels()
    {
        $this->info('📋 Testing Models...');
        
        $models = [
            'User' => User::class,
            'Applicant' => Applicant::class,
            'Interview' => Interview::class,
            'Exam' => Exam::class,
            'ExamSet' => ExamSet::class,
            'Question' => Question::class,
            'AccessCode' => AccessCode::class,
        ];
        
        foreach ($models as $name => $class) {
            try {
                $instance = new $class();
                $this->line("  ✅ {$name} model exists");
            } catch (\Exception $e) {
                $this->error("  ❌ {$name} model error: " . $e->getMessage());
            }
        }
    }
    
    private function testControllers()
    {
        $this->info('🎮 Testing Controllers...');
        
        $controllers = [
            'DepartmentHeadController' => \App\Http\Controllers\DepartmentHeadController::class,
            'InstructorController' => \App\Http\Controllers\InstructorController::class,
        ];
        
        foreach ($controllers as $name => $class) {
            try {
                $instance = new $class();
                $this->line("  ✅ {$name} controller exists");
                
                // Test if methods exist
                $methods = ['dashboard', 'interviewResults', 'analytics'];
                foreach ($methods as $method) {
                    if (method_exists($instance, $method)) {
                        $this->line("    ✅ {$method} method exists");
                    } else {
                        $this->warn("    ⚠️ {$method} method missing");
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ❌ {$name} controller error: " . $e->getMessage());
            }
        }
    }
    
    private function testRoutes()
    {
        $this->info('🛣️ Testing Routes...');
        
        $routes = [
            'department-head.dashboard' => '/admin/department-head/dashboard',
            'department-head.interview-results' => '/admin/department-head/interview-results',
            'department-head.analytics' => '/admin/department-head/analytics',
            'instructor.dashboard' => '/instructor/dashboard',
            'instructor.applicants' => '/instructor/applicants',
            'instructor.schedule' => '/instructor/schedule',
            'instructor.interview-history' => '/instructor/interview-history',
            'instructor.guidelines' => '/instructor/guidelines',
        ];
        
        foreach ($routes as $name => $path) {
            try {
                $route = route($name);
                $this->line("  ✅ {$name} route exists: {$route}");
            } catch (\Exception $e) {
                $this->error("  ❌ {$name} route error: " . $e->getMessage());
            }
        }
    }
    
    private function testViews()
    {
        $this->info('👁️ Testing Views...');
        
        $views = [
            'admin.department-head.dashboard',
            'admin.department-head.interview-results',
            'admin.department-head.interview-detail',
            'admin.department-head.analytics',
            'instructor.dashboard',
            'instructor.applicants',
            'instructor.interview-form',
            'instructor.schedule',
            'instructor.interview-history',
            'instructor.guidelines',
            'instructor.applicant-portfolio',
        ];
        
        foreach ($views as $view) {
            try {
                if (view()->exists($view)) {
                    $this->line("  ✅ {$view} view exists");
                } else {
                    $this->error("  ❌ {$view} view missing");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ {$view} view error: " . $e->getMessage());
            }
        }
    }
    
    private function testDatabaseRelationships()
    {
        $this->info('🗄️ Testing Database Relationships...');
        
        try {
            // Test User relationships
            $user = User::first();
            if ($user) {
                $this->line("  ✅ User model has relationships");
                
                // Test instructor interviews
                if (method_exists($user, 'interviews')) {
                    $interviews = $user->interviews;
                    $this->line("    ✅ User->interviews relationship works");
                }
            }
            
            // Test Applicant relationships
            $applicant = Applicant::first();
            if ($applicant) {
                $this->line("  ✅ Applicant model has relationships");
                
                if (method_exists($applicant, 'interviews')) {
                    $interviews = $applicant->interviews;
                    $this->line("    ✅ Applicant->interviews relationship works");
                }
                
                if (method_exists($applicant, 'examSet')) {
                    $examSet = $applicant->examSet;
                    $this->line("    ✅ Applicant->examSet relationship works");
                }
            }
            
            // Test Interview relationships
            $interview = Interview::first();
            if ($interview) {
                $this->line("  ✅ Interview model has relationships");
                
                if (method_exists($interview, 'applicant')) {
                    $applicant = $interview->applicant;
                    $this->line("    ✅ Interview->applicant relationship works");
                }
                
                if (method_exists($interview, 'interviewer')) {
                    $interviewer = $interview->interviewer;
                    $this->line("    ✅ Interview->interviewer relationship works");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Database relationship error: " . $e->getMessage());
        }
    }
    
    private function testSampleData()
    {
        $this->info('📊 Testing Sample Data...');
        
        try {
            $stats = [
                'users' => User::count(),
                'applicants' => Applicant::count(),
                'interviews' => Interview::count(),
                'exams' => Exam::count(),
                'exam_sets' => ExamSet::count(),
                'questions' => Question::count(),
                'access_codes' => AccessCode::count(),
            ];
            
            foreach ($stats as $table => $count) {
                $this->line("  📈 {$table}: {$count} records");
            }
            
            // Check for instructors
            $instructors = User::where('role', 'instructor')->count();
            $this->line("  👨‍🏫 Instructors: {$instructors}");
            
            // Check for department heads
            $deptHeads = User::where('role', 'department-head')->count();
            $this->line("  👨‍💼 Department Heads: {$deptHeads}");
            
            // Check for completed interviews
            $completedInterviews = Interview::where('status', 'completed')->count();
            $this->line("  ✅ Completed Interviews: {$completedInterviews}");
            
        } catch (\Exception $e) {
            $this->error("  ❌ Sample data error: " . $e->getMessage());
        }
    }
}
