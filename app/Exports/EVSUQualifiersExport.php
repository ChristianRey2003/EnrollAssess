<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\Settings as AppSettings;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class EVSUQualifiersExport
{
    protected $applicants;
    protected $filters;

    public function __construct($applicants, $filters = [])
    {
        $this->applicants = $applicants;
        $this->filters = $filters;
    }

    /**
     * Export qualifiers list to Word document using TemplateProcessor
     * This approach preserves template formatting 100% like XLSX export
     */
    public function export()
    {
        $templateProcessor = $this->loadAndFillTemplate();

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'evsu_qualifiers_');
        $templateProcessor->saveAs($tempFile);

        return $tempFile;
    }

    /**
     * Export qualifiers list as PDF using LibreOffice headless (exact DOCX→PDF match)
     */
    public function exportPdf()
    {
        // Step 1: Create the filled DOCX file
        $templateProcessor = $this->loadAndFillTemplate();

        // Save DOCX to temporary file
        $tempDocx = tempnam(sys_get_temp_dir(), 'evsu_qualifiers_docx_') . '.docx';
        $templateProcessor->saveAs($tempDocx);

        // Step 2: Convert DOCX to PDF using LibreOffice headless
        $tempPdf = tempnam(sys_get_temp_dir(), 'evsu_qualifiers_pdf_') . '.pdf';
        $this->convertDocxToPdfWithLibreOffice($tempDocx, $tempPdf);

        // Clean up temporary DOCX file
        @unlink($tempDocx);

        return $tempPdf;
    }

    /**
     * Convert DOCX to PDF using LibreOffice headless mode
     */
    protected function convertDocxToPdfWithLibreOffice($docxPath, $pdfPath)
    {
        // Detect LibreOffice executable
        $soffice = $this->findLibreOfficeExecutable();
        
        if (!$soffice) {
            throw new \RuntimeException(
                'LibreOffice not found. Please install LibreOffice or set LIBREOFFICE_PATH in .env'
            );
        }

        // Get output directory
        $outputDir = dirname($pdfPath);
        
        // Build command: convert to PDF in output directory
        $command = sprintf(
            '%s --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($soffice),
            escapeshellarg($outputDir),
            escapeshellarg($docxPath)
        );

        \Log::info('LibreOffice conversion command (Qualifiers)', ['command' => $command]);

        // Execute conversion
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            \Log::error('LibreOffice conversion failed (Qualifiers)', [
                'return_code' => $returnCode,
                'output' => $output
            ]);
            throw new \RuntimeException('PDF conversion failed: ' . implode("\n", $output));
        }

        // LibreOffice creates filename.pdf in the output directory
        $basename = pathinfo($docxPath, PATHINFO_FILENAME);
        $generatedPdf = $outputDir . DIRECTORY_SEPARATOR . $basename . '.pdf';

        // Move to the expected output path if different
        if ($generatedPdf !== $pdfPath && file_exists($generatedPdf)) {
            rename($generatedPdf, $pdfPath);
        }

        if (!file_exists($pdfPath)) {
            throw new \RuntimeException('PDF file was not created by LibreOffice');
        }

        \Log::info('LibreOffice PDF conversion successful (Qualifiers)', ['output' => $pdfPath]);
    }

    /**
     * Find LibreOffice executable on the system
     */
    protected function findLibreOfficeExecutable()
    {
        // Check environment variable first
        if ($envPath = env('LIBREOFFICE_PATH')) {
            if (file_exists($envPath)) {
                return $envPath;
            }
        }

        // Common paths on different OS
        $possiblePaths = [
            // Windows
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
            // Linux
            '/usr/bin/soffice',
            '/usr/bin/libreoffice',
            // macOS
            '/Applications/LibreOffice.app/Contents/MacOS/soffice',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try 'which' command on Unix-like systems
        if (PHP_OS_FAMILY !== 'Windows') {
            exec('which soffice 2>/dev/null', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                return trim($output[0]);
            }
            
            exec('which libreoffice 2>/dev/null', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                return trim($output[0]);
            }
        }

        return null;
    }

    /**
     * Load template and fill with data - shared logic for DOCX and PDF export
     */
    protected function loadAndFillTemplate()
    {
        // Official template path
        $template = resource_path('reports/templates/qualifiers_template.docx');
        
        // Fallback to project root
        if (!file_exists($template)) {
            $template = base_path('WORD-TEMPLATE.docx');
        }

        if (!file_exists($template)) {
            throw new \Exception('Word template not found at: ' . $template);
        }

        // Load template with TemplateProcessor (preserves formatting)
        $templateProcessor = new TemplateProcessor($template);

        // Fill header information placeholders
        $templateProcessor->setValue('campus', $this->filters['campus'] ?? 'Ormoc/Computer Studies');
        $templateProcessor->setValue('program_code', $this->filters['program_code'] ?? 'BSIT');
        $templateProcessor->setValue('program_description', $this->filters['program_description'] ?? 'Bachelor of Science in Information Technology');
        $templateProcessor->setValue('academic_year', $this->filters['academic_year'] ?? (date('Y') . '-' . (date('Y') + 1)));
        $templateProcessor->setValue('date_of_release', $this->filters['date_of_release'] ?? '');

        // Fill document information placeholders
        $templateProcessor->setValue('control_no', AppSettings::getSetting('report_control_no', 'EVSU- SASO-F-131'));
        $templateProcessor->setValue('revision_no', AppSettings::getSetting('report_revision_no', '0'));
        $templateProcessor->setValue('date', now()->format('Y-m-d'));

        // Fill signature placeholders from database settings
        // Get values and ensure they're uppercase for names
        $preparedByName = strtoupper(AppSettings::getSetting('report_signature_prepared_by_name', 'JOSEPH JAYMEL S. MORPOS'));
        $preparedByTitle = AppSettings::getSetting('report_signature_prepared_by_title', 'Head, Computer Studies Department');
        $notedName = strtoupper(AppSettings::getSetting('report_signature_noted_name', 'DR. JEFFRY V. OCAY'));
        $notedTitle = AppSettings::getSetting('report_signature_noted_title', 'Director, Ormoc Campus');
        $recommendingName = strtoupper(AppSettings::getSetting('report_signature_recommending_name', 'LYDIA M. MORANTE, D.A.'));
        $recommendingTitle = AppSettings::getSetting('report_signature_recommending_title', 'Vice President for Academic Affairs');
        $approvedName = strtoupper(AppSettings::getSetting('report_signature_approved_name', 'DENNIS C. DE PAZ, Ph.D.'));
        $approvedTitle = AppSettings::getSetting('report_signature_approved_title', 'University President');
        
        // Set values - Template uses ${variable} format (with $) for signatures
        // PhpWord TemplateProcessor handles ${variable} format natively
        // First, check what variables are detected in the template (for debugging)
        try {
            $detectedVariables = $templateProcessor->getVariables();
            \Log::info('Detected variables in template', ['variables' => $detectedVariables]);
        } catch (\Exception $e) {
            \Log::debug('Could not get template variables', ['error' => $e->getMessage()]);
        }
        
        // Replace signature placeholders
        // PhpWord TemplateProcessor should handle {variable} format automatically
        $templateProcessor->setValue('prepared_by_name', $preparedByName);
        $templateProcessor->setValue('prepared_by_title', $preparedByTitle);
        $templateProcessor->setValue('noted_name', $notedName);
        $templateProcessor->setValue('noted_title', $notedTitle);
        $templateProcessor->setValue('recommending_name', $recommendingName);
        $templateProcessor->setValue('recommending_title', $recommendingTitle);
        $templateProcessor->setValue('approved_name', $approvedName);
        $templateProcessor->setValue('approved_title', $approvedTitle);
        
        \Log::info('Set signature placeholders in DOCX', [
            'prepared_by_name' => $preparedByName,
            'noted_name' => $notedName,
            'recommending_name' => $recommendingName,
            'approved_name' => $approvedName,
        ]);

        // Clone the data table row for each applicant
        $applicantCount = count($this->applicants);
        
        if ($applicantCount > 0) {
            // Clone row using the first placeholder variable as identifier
            $templateProcessor->cloneRow('no', $applicantCount);

            // Fill each cloned row with applicant data
            foreach ($this->applicants as $index => $applicant) {
                $rowNumber = $index + 1; // TemplateProcessor uses 1-based indexing
                
                // Fill placeholders: variable#1, variable#2, etc.
                $templateProcessor->setValue('no#' . $rowNumber, $rowNumber);
                $templateProcessor->setValue('application_no#' . $rowNumber, $applicant->application_no ?? '');
                $templateProcessor->setValue('preferred_program#' . $rowNumber, $applicant->preferred_course ?? 'BSIT');
                $templateProcessor->setValue('last_name#' . $rowNumber, strtoupper($applicant->last_name ?? ''));
                $templateProcessor->setValue('first_name#' . $rowNumber, strtoupper($applicant->first_name ?? ''));
                $templateProcessor->setValue('middle_name#' . $rowNumber, strtoupper($applicant->middle_name ?? ''));
            }
        }

        return $templateProcessor;
    }

    /**
     * Manually replace placeholders if setValue() doesn't work
     * This is a fallback method
     */
    protected function replacePlaceholdersManually($templateProcessor, $replacements)
    {
        try {
            // Get the document XML
            $document = $templateProcessor->getDocument();
            
            // Replace placeholders in the XML directly
            foreach ($replacements as $placeholder => $value) {
                // Try different placeholder formats
                $patterns = [
                    '{' . $placeholder . '}',
                    '${' . $placeholder . '}',
                    '{$' . $placeholder . '}',
                ];
                
                foreach ($patterns as $pattern) {
                    // This is a workaround - TemplateProcessor should handle this, but if it doesn't,
                    // we'll need to access the underlying XML
                    \Log::debug('Attempting manual replacement', [
                        'pattern' => $pattern,
                        'value' => $value
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Manual placeholder replacement failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}

