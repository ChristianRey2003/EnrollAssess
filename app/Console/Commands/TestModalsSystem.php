<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TestModalsSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:modals-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test and identify modal issues across the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== EnrollAssess Modal System Analysis ===');
        $this->newLine();

        // Find all files with modals
        $modalFiles = $this->findModalFiles();
        
        $this->info('1. Modal Files Found:');
        foreach ($modalFiles as $file => $modals) {
            $this->line("   📄 {$file}");
            foreach ($modals as $modal) {
                $this->line("     - {$modal}");
            }
        }
        $this->newLine();

        // Check for common issues
        $this->info('2. Common Modal Issues Analysis:');
        
        $issues = [];
        
        // Check for missing CSS
        foreach ($modalFiles as $file => $modals) {
            $content = File::get(base_path($file));
            
            // Check for CSS variables
            if (strpos($content, '--white') === false && strpos($content, 'var(--white)') !== false) {
                $issues[] = "{$file}: Missing CSS variables definition";
            }
            
            // Check for modal overlay styles
            if (strpos($content, '.modal-overlay') === false && strpos($content, 'modal-overlay') !== false) {
                $issues[] = "{$file}: Missing modal-overlay CSS styles";
            }
            
            // Check for JavaScript functions
            foreach ($modals as $modalId) {
                $functionName = 'show' . ucfirst(str_replace(['Modal', 'modal'], '', $modalId)) . 'Modal';
                if (strpos($content, $functionName) === false) {
                    $issues[] = "{$file}: Missing JavaScript function {$functionName}";
                }
            }
        }

        if (empty($issues)) {
            $this->line("   ✅ No common issues detected");
        } else {
            foreach ($issues as $issue) {
                $this->line("   ❌ {$issue}");
            }
        }
        $this->newLine();

        // Check specific modal functionality
        $this->info('3. Modal Functionality Analysis:');
        
        $modalChecks = [
            'Generate Access Codes Modal' => [
                'file' => 'resources/views/admin/applicants/index.blade.php',
                'modal_id' => 'generateCodesModal',
                'trigger_function' => 'showGenerateAccessCodesModal',
                'close_function' => 'closeGenerateCodesModal',
                'confirm_function' => 'confirmGenerateAccessCodes'
            ],
            'Assign Exam Sets Modal' => [
                'file' => 'resources/views/admin/applicants/index.blade.php',
                'modal_id' => 'assignSetsModal',
                'trigger_function' => 'showAssignExamSetsModal',
                'close_function' => 'closeAssignSetsModal',
                'confirm_function' => 'confirmAssignExamSets'
            ],
            'Delete Confirmation Modal' => [
                'file' => 'resources/views/admin/questions.blade.php',
                'modal_id' => 'deleteModal',
                'trigger_function' => 'showDeleteModal',
                'close_function' => 'closeDeleteModal',
                'confirm_function' => 'confirmDelete'
            ]
        ];

        foreach ($modalChecks as $modalName => $config) {
            $this->line("   🔍 {$modalName}:");
            
            if (File::exists(base_path($config['file']))) {
                $content = File::get(base_path($config['file']));
                
                $checks = [
                    "Modal HTML ({$config['modal_id']})" => strpos($content, $config['modal_id']) !== false,
                    "Trigger function ({$config['trigger_function']})" => strpos($content, $config['trigger_function']) !== false,
                    "Close function ({$config['close_function']})" => strpos($content, $config['close_function']) !== false,
                    "Confirm function ({$config['confirm_function']})" => strpos($content, $config['confirm_function']) !== false,
                    "CSRF token check" => strpos($content, 'csrf-token') !== false,
                    "Modal overlay class" => strpos($content, 'modal-overlay') !== false,
                ];
                
                foreach ($checks as $check => $result) {
                    $status = $result ? '✅' : '❌';
                    $this->line("     {$status} {$check}");
                }
            } else {
                $this->line("     ❌ File not found: {$config['file']}");
            }
            $this->newLine();
        }

        // Provide solutions
        $this->info('4. Recommended Solutions:');
        $solutions = [
            'Add CSS variables' => 'Define --white, --maroon-primary, etc. in :root{}',
            'Add modal CSS' => 'Include .modal-overlay, .modal-content styles',
            'Check JavaScript syntax' => 'Verify no missing semicolons or brackets',
            'Verify CSRF tokens' => 'Ensure meta[name="csrf-token"] is present',
            'Test modal z-index' => 'Set z-index: 1000+ for modal-overlay',
            'Add event listeners' => 'Ensure click outside to close functionality',
        ];

        foreach ($solutions as $solution => $description) {
            $this->line("   💡 {$solution}: {$description}");
        }
        $this->newLine();

        $this->info('=== Modal System Analysis Complete ===');
        
        return 0;
    }

    private function findModalFiles()
    {
        $files = [];
        $viewsPath = resource_path('views');
        
        $phpFiles = File::allFiles($viewsPath);
        
        foreach ($phpFiles as $file) {
            $content = File::get($file->getPathname());
            
            // Find modal IDs
            preg_match_all('/id="([^"]*[Mm]odal[^"]*)"/', $content, $matches);
            
            if (!empty($matches[1])) {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $files[$relativePath] = $matches[1];
            }
        }
        
        return $files;
    }
}
