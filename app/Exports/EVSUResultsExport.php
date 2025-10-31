<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Services\AdmissionScoringService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EVSUResultsExport
{
    protected $applicants;
    protected $filters;
    protected $scoringService;

    public function __construct($applicants, $filters = [])
    {
        $this->applicants = $applicants;
        $this->filters = $filters;
        $this->scoringService = new AdmissionScoringService();
    }

    /**
     * Export directly - load template, populate data table only, leave header fields empty for manual editing
     */
    public function export()
    {
        // Use the new official template in project root
        $newTemplate = base_path('NEW TEMPLATE.xlsx');
        $oldTemplate = base_path('130-GSO-Form-EVALUTION-RESULTS-OF-ENTRANCE-OR-ADMISSION-FOR-NEW-OR-FRESHMEN-AND-TRANSFEREE-APPLICANTS_BSIT_Ormoc.xlsx');
        $fallbackTemplate = resource_path('reports/templates/evsu_results_template.xlsx');
        
        // Priority: NEW TEMPLATE.xlsx > old template > fallback
        if (file_exists($newTemplate)) {
            $templatePath = $newTemplate;
        } elseif (file_exists($oldTemplate)) {
            $templatePath = $oldTemplate;
        } else {
            $templatePath = $fallbackTemplate;
        }
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // DO NOT fill header fields - leave Control No., Date, Academic Year, Date of Release empty
        // User will manually edit these fields after download

        // Fill ONLY the data table rows
        $this->fillDataRows($sheet);

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'evsu_export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Fill data rows with applicant information
     * Starts after the table header row (row 14 based on template structure)
     */
    protected function fillDataRows($sheet)
    {
        // Find the data table header row by looking for "No." in column A
        $headerRow = null;
        for ($row = 1; $row <= 20; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if ($cellValue === 'No.' || stripos($cellValue, 'No.') !== false) {
                $headerRow = $row;
                break;
            }
        }

        if (!$headerRow) {
            // Fallback to row 14 if header not found
            $headerRow = 14;
        }

        // Data starts on the row after the header
        $startRow = $headerRow + 1;
        $currentRow = $startRow;

        // Get the template row style for copying
        $templateRow = $startRow;

        // Clear any existing sample data in the template body (keep styles/merges)
        $highestRow = $sheet->getHighestRow();
        for ($row = $startRow; $row <= $highestRow; $row++) {
            for ($col = 'A'; $col <= 'L'; $col++) {
                $sheet->setCellValue($col . $row, '');
            }
        }

        foreach ($this->applicants as $index => $applicant) {
            // Calculate overall rating if all scores available
            // UEE and GWA are already weighted: UEE (0–60), GWA (0–30)
            $ueeScore = (float) ($applicant->score ?? 0);
            $gwaScore = (float) ($applicant->card_tor_gwa ?? 0);
            $interviewSkillWeighted = 0.0; // 0–10

            if ($this->scoringService->hasAllRequiredScores($applicant)) {
                // Interview/Skill (10%) = average(EnrollAssess, Interview) × 0.10
                $enrollAssessScore = (float) ($applicant->enrollassess_score ?? 0);
                $interviewScore = (float) ($applicant->interview_score ?? 0);
                $interviewSkillWeighted = (($enrollAssessScore + $interviewScore) / 2.0) * 0.10;
            }

            $overall = $ueeScore + $gwaScore + $interviewSkillWeighted;

            // Column mapping based on template header:
            // A: No.
            // B: Application No.
            // C: Preferred Program
            // D: Last Name
            // E: First Name
            // F: Middle Name
            // G: E-mail
            // H: Contact Number
            // I: University Entrance Examination (60%)
            // J: Card/TOR GWA (30%)
            // K: Interview/Skill Test (10%)
            // L: Overall Rating (From Highest to Lowest)

            // If not first row, copy style from template row
            if ($currentRow > $startRow) {
                $sheet->insertNewRowBefore($currentRow, 1);
                // Copy row style
                for ($col = 'A'; $col <= 'L'; $col++) {
                    $sourceStyle = $sheet->getStyle($col . $templateRow)->getFont();
                    $sourceBorders = $sheet->getStyle($col . $templateRow)->getBorders();
                    $sourceAlignment = $sheet->getStyle($col . $templateRow)->getAlignment();
                    
                    $sheet->getStyle($col . $currentRow)->applyFromArray([
                        'font' => [
                            'name' => $sourceStyle->getName(),
                            'size' => $sourceStyle->getSize(),
                            'bold' => $sourceStyle->getBold(),
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => $sourceAlignment->getHorizontal(),
                            'vertical' => $sourceAlignment->getVertical(),
                        ],
                    ]);
                }
            }

            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $applicant->application_no);
            $sheet->setCellValue('C' . $currentRow, $applicant->preferred_course ?? 'BSIT');
            $sheet->setCellValue('D' . $currentRow, strtoupper($applicant->last_name));
            $sheet->setCellValue('E' . $currentRow, strtoupper($applicant->first_name));
            $sheet->setCellValue('F' . $currentRow, strtoupper($applicant->middle_name ?? ''));
            $sheet->setCellValue('G' . $currentRow, $applicant->email_address);
            $sheet->setCellValue('H' . $currentRow, $applicant->phone_number);
            $sheet->setCellValue('I' . $currentRow, number_format($ueeScore, 2));
            $sheet->setCellValue('J' . $currentRow, number_format($gwaScore, 2));
            // Column K expects the 10% contribution (0–10)
            $sheet->setCellValue('K' . $currentRow, number_format($interviewSkillWeighted, 2));
            // Column L is the overall sum 0–100
            $sheet->setCellValue('L' . $currentRow, number_format($overall, 2));

            $currentRow++;
        }
    }
}

