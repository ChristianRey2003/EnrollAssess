<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Services\AdmissionScoringService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

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
        $spreadsheet = $this->loadAndFillTemplate();

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'evsu_export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Export as PDF using LibreOffice headless conversion (Excel-compatible rendering)
     */
    public function exportPdf()
    {
        $spreadsheet = $this->loadAndFillTemplate();

        // Configure page setup for PDF export
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set column widths matching user's Excel settings exactly
        // Source: Excel Legal Landscape (8.5" x 14") with 130% scale
        $columnWidths = [
            'A' => 6,      // No.
            'B' => 17.5,   // Application No.
            'C' => 9.17,   // Preferred Program
            'D' => 17.17,  // Last Name
            'E' => 19,     // First Name
            'F' => 12.5,   // Middle Name
            'G' => 26.83,  // E-mail
            'H' => 11.5,   // Contact Number
            'I' => 18.5,   // UEE (60%)
            'J' => 12,     // Card/TOR GWA (30%)
            'K' => 15.83,  // Interview/Skill Test (10%)
            'L' => 12.5,   // Overall Rating
        ];
        
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Configure page setup - Legal 8.5x14 with Fit to width
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL)
            ->setFitToWidth(1)   // Fit to one page width
            ->setFitToHeight(0)  // Unlimited height (multiple pages)
            ->setHorizontalCentered(false)
            ->setVerticalCentered(false);

        // Narrow margins
        $margins = $sheet->getPageMargins();
        $margins->setTop(0.25);
        $margins->setBottom(0.25);
        $margins->setLeft(0.25);
        $margins->setRight(0.25);
        $margins->setHeader(0.2);
        $margins->setFooter(0.2);

        // Define print area
        $lastRow = $sheet->getHighestDataRow('A');
        if ($lastRow > 0) {
            $sheet->getPageSetup()->setPrintArea("A1:L{$lastRow}");
        }

        // Hide gridlines
        $sheet->setShowGridlines(false);

        // Step 1: Save the configured XLSX to a temporary file
        $tempXlsx = tempnam(sys_get_temp_dir(), 'evsu_xlsx_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempXlsx);

        // Step 2: Convert XLSX to PDF using LibreOffice headless
        $tempPdf = tempnam(sys_get_temp_dir(), 'evsu_pdf_') . '.pdf';
        $this->convertXlsxToPdfWithLibreOffice($tempXlsx, $tempPdf);

        // Clean up temporary XLSX
        @unlink($tempXlsx);

        return $tempPdf;
    }

    /**
     * Convert XLSX to PDF using LibreOffice headless mode
     */
    protected function convertXlsxToPdfWithLibreOffice($xlsxPath, $pdfPath)
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
        // LibreOffice will create a file with .pdf extension based on the input filename
        $command = sprintf(
            '%s --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($soffice),
            escapeshellarg($outputDir),
            escapeshellarg($xlsxPath)
        );

        \Log::info('LibreOffice conversion command', ['command' => $command]);

        // Execute conversion
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            \Log::error('LibreOffice conversion failed', [
                'return_code' => $returnCode,
                'output' => $output
            ]);
            throw new \RuntimeException('PDF conversion failed: ' . implode("\n", $output));
        }

        // LibreOffice creates filename.pdf in the output directory
        $basename = pathinfo($xlsxPath, PATHINFO_FILENAME);
        $generatedPdf = $outputDir . DIRECTORY_SEPARATOR . $basename . '.pdf';

        // Move to the expected output path if different
        if ($generatedPdf !== $pdfPath && file_exists($generatedPdf)) {
            rename($generatedPdf, $pdfPath);
        }

        if (!file_exists($pdfPath)) {
            throw new \RuntimeException('PDF file was not created by LibreOffice');
        }

        \Log::info('LibreOffice PDF conversion successful', ['output' => $pdfPath]);
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
     * Load template and fill with data - shared logic for XLSX and PDF export
     */
    protected function loadAndFillTemplate()
    {
        // Use only the official NEW TEMPLATE.xlsx inside resources
        $templatePath = resource_path('reports/templates/NEW TEMPLATE.xlsx');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Template not found at: ' . $templatePath . ' (expected NEW TEMPLATE.xlsx in resources/reports/templates)');
        }
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // DO NOT fill header fields - leave Control No., Date, Academic Year, Date of Release empty
        // User will manually edit these fields after download

        // Fill ONLY the data table rows
        $this->fillDataRows($sheet);

        // Do not alter XLSX page setup or margins. The template dictates XLSX layout.
        return $spreadsheet;
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
        // IMPORTANT: Do not clear the signature blocks (Prepared by / Noted / Recommending Approval / Approved)
        $highestRow = $sheet->getHighestRow();
        $signatureStartRow = null;
        for ($row = $startRow; $row <= $highestRow; $row++) {
            for ($col = 'A'; $col <= 'L'; $col++) {
                $value = (string) $sheet->getCell($col . $row)->getValue();
                if ($value !== '' && stripos($value, 'Prepared by') !== false) {
                    $signatureStartRow = $row;
                    break 2; // stop scanning once we find the signature start
                }
            }
        }

        $clearUntilRow = $signatureStartRow ? ($signatureStartRow - 1) : $highestRow;
        for ($row = $startRow; $row <= $clearUntilRow; $row++) {
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

        // REMOVE ALL EMPTY ROWS: Delete all empty rows between last applicant and signature block
        $lastApplicantRow = $currentRow - 1;
        
        // Find signature block start row again (row numbers may have shifted due to insertNewRowBefore)
        $highestRow = $sheet->getHighestRow();
        $signatureStartRow = null;
        for ($row = $lastApplicantRow + 1; $row <= $highestRow; $row++) {
            for ($col = 'A'; $col <= 'L'; $col++) {
                $value = (string) $sheet->getCell($col . $row)->getValue();
                if ($value !== '' && (stripos($value, 'Prepared by') !== false || stripos($value, 'Noted') !== false)) {
                    $signatureStartRow = $row;
                    break 2;
                }
            }
        }

        // Delete ALL empty rows between last applicant and signature block
        if ($signatureStartRow && $signatureStartRow > ($lastApplicantRow + 1)) {
            $rowsToDelete = $signatureStartRow - $lastApplicantRow - 1;
            if ($rowsToDelete > 0) {
                $sheet->removeRow($lastApplicantRow + 1, $rowsToDelete);
                \Log::info('Removed all empty rows from EVSU export', [
                    'last_applicant_row' => $lastApplicantRow,
                    'rows_deleted' => $rowsToDelete,
                    'total_applicants' => count($this->applicants)
                ]);
            }
        }

        // ADD INVISIBLE SPACER ROWS: Create spacing without visible borders
        // Insert 2 invisible spacer rows after the last applicant for visual separation
        $spacerRowsCount = 2;
        $spacerStartRow = $lastApplicantRow + 1;
        
        // Insert spacer rows
        $sheet->insertNewRowBefore($spacerStartRow, $spacerRowsCount);
        
        // Configure spacer rows: remove all borders and set height for spacing
        for ($i = 0; $i < $spacerRowsCount; $i++) {
            $spacerRow = $spacerStartRow + $i;
            
            // Set row height for visual spacing (default is ~15, use 20-25 for spacing)
            $sheet->getRowDimension($spacerRow)->setRowHeight(22);
            
            // Remove all borders from spacer row cells
            for ($col = 'A'; $col <= 'L'; $col++) {
                $sheet->getStyle($col . $spacerRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                    ],
                ]);
                // Ensure cell is empty
                $sheet->setCellValue($col . $spacerRow, '');
            }
        }
        
        \Log::info('Added invisible spacer rows', [
            'spacer_rows' => $spacerRowsCount,
            'spacer_start_row' => $spacerStartRow,
            'row_height' => 22
        ]);
    }
}

