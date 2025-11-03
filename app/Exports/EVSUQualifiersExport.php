<?php

namespace App\Exports;

use App\Models\Applicant;
use PhpOffice\PhpWord\TemplateProcessor;

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

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'evsu_qualifiers_');
        $templateProcessor->saveAs($tempFile);

        return $tempFile;
    }
}

