<?php

namespace App\Exports;

use App\Models\Applicant;
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
}

