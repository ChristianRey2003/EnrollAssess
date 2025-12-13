<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\Settings;
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
        
        // IMPORTANT: Do NOT override column widths - preserve template's original column widths
        // The template (NEW TEMPLATE.xlsx) has carefully set column widths that should be used as-is
        // Previously hardcoded widths were causing layout issues (e.g., "No." column too wide)

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
        
        // Create a temporary user installation directory for LibreOffice
        // This prevents permission issues when running as web server user
        // Use a more isolated path to avoid conflicts with default LibreOffice installation
        $userInstallDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'libreoffice_user_' . uniqid();
        if (!is_dir($userInstallDir)) {
            if (!@mkdir($userInstallDir, 0755, true)) {
                throw new \RuntimeException('Failed to create LibreOffice user installation directory: ' . $userInstallDir);
            }
        }
        
        // Ensure the directory is writable
        if (!is_writable($userInstallDir)) {
            throw new \RuntimeException('LibreOffice user installation directory is not writable: ' . $userInstallDir);
        }

        // Set environment variables to prevent dconf and Java errors
        // On Linux, use 'env' command to set environment variables properly
        $envVars = [
            'HOME=' . sys_get_temp_dir(),
            'USER=' . (get_current_user() ?: 'www-data'),
            'USERNAME=' . (get_current_user() ?: 'www-data'),
            'NO_AT_BRIDGE=1', // Suppress dconf warnings
            'SAL_USE_VCLPLUGIN=headless', // Force headless mode
        ];

        // Build command: convert to PDF with custom user installation directory
        // Use -env:UserInstallation to specify a writable directory
        // Convert path to file:// URL format
        // Windows needs file:/// (three slashes) for absolute paths
        // Linux needs file:/// (three slashes) for absolute paths
        $normalizedPath = str_replace('\\', '/', $userInstallDir);
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: file:///C:/path/to/dir
            $userInstallUrl = 'file:///' . $normalizedPath;
        } else {
            // Linux/Mac: file:///path/to/dir
            $userInstallUrl = 'file:///' . $normalizedPath;
        }
        
        // On Linux, prefix with 'env' to set environment variables
        if (PHP_OS_FAMILY !== 'Windows') {
            $envString = 'env ' . implode(' ', array_map('escapeshellarg', $envVars)) . ' ';
            $command = sprintf(
                '%s%s --headless -nodefault -env:UserInstallation=%s --convert-to pdf --outdir %s %s 2>&1',
                $envString,
                escapeshellarg($soffice),
                escapeshellarg($userInstallUrl),
                escapeshellarg($outputDir),
                escapeshellarg($xlsxPath)
            );
        } else {
            // Windows: Use cmd /c to properly execute batch commands
            // Build environment variable string without quotes around values (Windows set command)
            $envString = '';
            foreach ($envVars as $envVar) {
                list($key, $value) = explode('=', $envVar, 2);
                // Escape special characters in value but don't add quotes
                $escapedValue = str_replace(['&', '|', '<', '>', '^'], ['^&', '^|', '^<', '^>', '^^'], $value);
                $envString .= 'set ' . $key . '=' . $escapedValue . ' && ';
            }
            // Wrap in cmd /c - the entire command chain needs to be in quotes
            // Add -nodefault to prevent LibreOffice from using default settings
            $sofficeCmd = escapeshellarg($soffice);
            $userInstallUrlEscaped = escapeshellarg($userInstallUrl);
            $outputDirEscaped = escapeshellarg($outputDir);
            $xlsxPathEscaped = escapeshellarg($xlsxPath);
            
            $command = sprintf(
                'cmd /c "%s%s --headless -nodefault -env:UserInstallation=%s --convert-to pdf --outdir %s %s" 2>&1',
                $envString,
                $sofficeCmd,
                $userInstallUrlEscaped,
                $outputDirEscaped,
                $xlsxPathEscaped
            );
        }

        \Log::info('LibreOffice conversion command', [
            'command' => $command,
            'soffice_path' => $soffice,
            'user_install_dir' => $userInstallDir,
            'user_install_url' => $userInstallUrl,
            'xlsx_path' => $xlsxPath,
            'output_dir' => $outputDir,
            'pdf_path' => $pdfPath
        ]);

        // Execute conversion
        exec($command, $output, $returnCode);

        // Clean up temporary user installation directory
        @$this->removeDirectory($userInstallDir);

        // Check if PDF was created even if return code is non-zero
        // (LibreOffice sometimes returns non-zero but still creates the PDF)
        $basename = pathinfo($xlsxPath, PATHINFO_FILENAME);
        $generatedPdf = $outputDir . DIRECTORY_SEPARATOR . $basename . '.pdf';

        if (file_exists($generatedPdf)) {
            // Move to the expected output path if different
            if ($generatedPdf !== $pdfPath) {
                rename($generatedPdf, $pdfPath);
            }
            \Log::info('LibreOffice PDF conversion successful', ['output' => $pdfPath]);
            return;
        }

        // If PDF was not created, log error and throw exception
        if ($returnCode !== 0) {
            \Log::error('LibreOffice conversion failed', [
                'return_code' => $returnCode,
                'output' => $output
            ]);
            throw new \RuntimeException('PDF conversion failed: ' . implode("\n", $output));
        }

        if (!file_exists($pdfPath)) {
            throw new \RuntimeException('PDF file was not created by LibreOffice');
        }
    }

    /**
     * Recursively remove a directory
     */
    protected function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
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

        // IMPORTANT: Fill data rows FIRST because it may insert/remove rows
        // which would shift the signature block row numbers
        $this->fillDataRows($sheet);
        
        // Fill signature settings AFTER data rows are filled
        // This ensures we're working with the correct row numbers
        $this->fillSignatureSettings($sheet);

        // Do not alter XLSX page setup or margins. The template dictates XLSX layout.
        return $spreadsheet;
    }

    /**
     * Fill signature settings in the template
     */
    protected function fillSignatureSettings($sheet)
    {
        // Get settings from database
        $controlNo = Settings::getSetting('report_control_no', 'EVSU- SASO-F-131');
        $revisionNo = Settings::getSetting('report_revision_no', '0');
        $preparedByName = Settings::getSetting('report_signature_prepared_by_name', 'JOSEPH JAYMEL S. MORPOS');
        $preparedByTitle = Settings::getSetting('report_signature_prepared_by_title', 'Head, Computer Studies Department');
        $notedName = Settings::getSetting('report_signature_noted_name', 'DR. JEFFRY V. OCAY');
        $notedTitle = Settings::getSetting('report_signature_noted_title', 'Director, Ormoc Campus');
        $recommendingName = Settings::getSetting('report_signature_recommending_name', 'LYDIA M. MORANTE, D.A.');
        $recommendingTitle = Settings::getSetting('report_signature_recommending_title', 'Vice President for Academic Affairs');
        $approvedName = Settings::getSetting('report_signature_approved_name', 'DENNIS C. DE PAZ, Ph.D.');
        $approvedTitle = Settings::getSetting('report_signature_approved_title', 'University President');

        // Fill document information (these are in fixed header rows, not affected by data insertion)
        $sheet->setCellValue('I3', $controlNo);
        $sheet->setCellValue('I4', $revisionNo);
        $sheet->setCellValue('I5', now()->format('Y-m-d')); // Date

        // Dynamically find signature label rows (they may have shifted due to data row insertion)
        $signatureRows = $this->findSignatureRows($sheet);
        
        // Default font sizes
        $nameFontSize = 12;
        $titleFontSize = 12;
        
        // Fill signature blocks based on dynamically found rows
        // Each signature has: Label row, then Name row (+3 from label), then Title row (+4 from label)
        
        if (isset($signatureRows['prepared_by'])) {
            $nameRow = $signatureRows['prepared_by'] + 3;
            $titleRow = $signatureRows['prepared_by'] + 4;
            $this->setSignatureName($sheet, 'A' . $nameRow, $preparedByName, $nameFontSize, true);
            $this->setSignatureTitle($sheet, 'A' . $titleRow, $preparedByTitle, $titleFontSize);
        }

        if (isset($signatureRows['noted'])) {
            $nameRow = $signatureRows['noted'] + 3;
            $titleRow = $signatureRows['noted'] + 4;
            $this->setSignatureName($sheet, 'A' . $nameRow, $notedName, $nameFontSize, true);
            $this->setSignatureTitle($sheet, 'A' . $titleRow, $notedTitle, $titleFontSize);
        }

        if (isset($signatureRows['recommending'])) {
            $nameRow = $signatureRows['recommending'] + 2;
            $titleRow = $signatureRows['recommending'] + 3;
            $this->setSignatureName($sheet, 'A' . $nameRow, $recommendingName, $nameFontSize, true);
            $this->setSignatureTitle($sheet, 'A' . $titleRow, $recommendingTitle, $titleFontSize);
        }

        if (isset($signatureRows['approved'])) {
            $nameRow = $signatureRows['approved'] + 2;
            $titleRow = $signatureRows['approved'] + 3;
            $this->setSignatureName($sheet, 'A' . $nameRow, $approvedName, $nameFontSize, true);
            $this->setSignatureTitle($sheet, 'A' . $titleRow, $approvedTitle, $titleFontSize);
        }
    }
    
    /**
     * Find signature label rows dynamically (they shift when data rows are inserted)
     */
    protected function findSignatureRows($sheet)
    {
        $signatureRows = [];
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 1; $row <= $highestRow; $row++) {
            $value = (string) $sheet->getCell('A' . $row)->getValue();
            $valueLower = strtolower(trim($value));
            
            if (strpos($valueLower, 'prepared by') !== false) {
                $signatureRows['prepared_by'] = $row;
            } elseif ($valueLower === 'noted:' || strpos($valueLower, 'noted:') !== false) {
                $signatureRows['noted'] = $row;
            } elseif (strpos($valueLower, 'recommending approval') !== false) {
                $signatureRows['recommending'] = $row;
            } elseif ($valueLower === 'approved:' || strpos($valueLower, 'approved:') !== false) {
                $signatureRows['approved'] = $row;
            }
        }
        
        \Log::info('Found signature rows', $signatureRows);
        
        return $signatureRows;
    }

    /**
     * Get font size from template cell
     */
    protected function getTemplateFontSize($sheet, $cell, $default = 12)
    {
        try {
            $style = $sheet->getStyle($cell);
            $font = $style->getFont();
            $size = $font->getSize();
            return $size > 0 ? $size : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Apply border to a cell or its merged range
     */
    protected function applyBorderToCellOrRange($sheet, $cell)
    {
        try {
            // Check if cell is part of a merged range
            $mergedRanges = $sheet->getMergeCells();
            $targetRange = $cell;
            
            foreach ($mergedRanges as $range) {
                if ($sheet->getCell($cell)->isInRange($range)) {
                    $targetRange = $range;
                    break;
                }
            }
            
            // Apply border to the cell or merged range
            $borders = $sheet->getStyle($targetRange)->getBorders();
            $borders->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $borders->getBottom()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
        } catch (\Exception $e) {
            \Log::debug('Could not apply border to cell', ['cell' => $cell, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Copy merge structure from source cell to target cell
     * This ensures borders match the text width correctly
     */
    protected function copyMergeStructure($sheet, $sourceCell, $targetCell)
    {
        try {
            // Check if source cell is part of a merged range
            $mergedRanges = $sheet->getMergeCells();
            
            // Log all merged ranges for debugging
            \Log::info('Checking merge structure', [
                'source_cell' => $sourceCell,
                'target_cell' => $targetCell,
                'total_merged_ranges' => count($mergedRanges),
                'all_ranges' => array_values($mergedRanges)
            ]);
            
            foreach ($mergedRanges as $range) {
                try {
                    $isInRange = $sheet->getCell($sourceCell)->isInRange($range);
                    \Log::info('Checking range', [
                        'range' => $range,
                        'source_cell' => $sourceCell,
                        'is_in_range' => $isInRange
                    ]);
                    
                    if ($isInRange) {
                        \Log::info('Found merge for source cell', ['source' => $sourceCell, 'range' => $range]);
                        
                        // Extract the range and apply same merge to target
                        // For example, if A144 is merged as A144:B144, merge A132 as A132:B132
                        $rangeParts = explode(':', $range);
                        if (count($rangeParts) === 2) {
                            $sourceStart = $rangeParts[0];
                            $sourceEnd = $rangeParts[1];
                            
                            // Get row numbers
                            $sourceRow = (int) filter_var($sourceCell, FILTER_SANITIZE_NUMBER_INT);
                            $targetRow = (int) filter_var($targetCell, FILTER_SANITIZE_NUMBER_INT);
                            $rowDiff = $targetRow - $sourceRow;
                            
                            // Calculate new range for target
                            $sourceStartCol = preg_replace('/[0-9]/', '', $sourceStart);
                            $sourceStartRowNum = (int) filter_var($sourceStart, FILTER_SANITIZE_NUMBER_INT);
                            $sourceEndCol = preg_replace('/[0-9]/', '', $sourceEnd);
                            $sourceEndRowNum = (int) filter_var($sourceEnd, FILTER_SANITIZE_NUMBER_INT);
                            
                            $targetStart = $sourceStartCol . ($sourceStartRowNum + $rowDiff);
                            $targetEnd = $sourceEndCol . ($sourceEndRowNum + $rowDiff);
                            $targetRange = $targetStart . ':' . $targetEnd;
                            
                            \Log::info('Calculated target merge range', [
                                'source_range' => $range,
                                'target_range' => $targetRange,
                                'row_diff' => $rowDiff
                            ]);
                            
                            // Unmerge target if already merged, then merge with new range
                            if ($sheet->getMergeCells()) {
                                foreach ($sheet->getMergeCells() as $existingRange) {
                                    try {
                                        if ($sheet->getCell($targetCell)->isInRange($existingRange)) {
                                            \Log::info('Unmerging existing range', ['range' => $existingRange]);
                                            $sheet->unmergeCells($existingRange);
                                            break;
                                        }
                                    } catch (\Exception $e) {
                                        \Log::debug('Error unmerging', ['range' => $existingRange, 'error' => $e->getMessage()]);
                                    }
                                }
                            }
                            
                            $sheet->mergeCells($targetRange);
                            \Log::info('Successfully copied merge structure', [
                                'source' => $range,
                                'target' => $targetRange
                            ]);
                        }
                        return; // Found and processed, exit
                    }
                } catch (\Exception $e) {
                    \Log::debug('Error checking range', ['range' => $range, 'error' => $e->getMessage()]);
                }
            }
            
            \Log::info('No merge found for source cell - it is a single cell', ['source' => $sourceCell]);
            
            // If source is not merged, ensure target is not merged either
            if ($sheet->getMergeCells()) {
                foreach ($sheet->getMergeCells() as $existingRange) {
                    try {
                        if ($sheet->getCell($targetCell)->isInRange($existingRange)) {
                            \Log::info('Unmerging target cell (source has no merge)', ['range' => $existingRange]);
                            $sheet->unmergeCells($existingRange);
                            break;
                        }
                    } catch (\Exception $e) {
                        \Log::debug('Error unmerging target', ['range' => $existingRange, 'error' => $e->getMessage()]);
                    }
                }
            }
        } catch (\Exception $e) {
            // If merge copying fails, continue without it
            \Log::error('Could not copy cell merge structure', [
                'source' => $sourceCell,
                'target' => $targetCell,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Set signature name with bold and uppercase formatting
     * Matches template style: BOLD and UPPERCASE
     * @param bool $addBorder Whether to add bottom border line (default: true)
     */
    protected function setSignatureName($sheet, $cell, $name, $fontSize = 12, $addBorder = true)
    {
        // Ensure name is uppercase (CAPS LOCK) - preserve full name
        $name = strtoupper(trim($name));
        
        // Extract row number from cell reference (e.g., 'A132' -> 132)
        $row = (int) filter_var($cell, FILTER_SANITIZE_NUMBER_INT);
        
        // Ensure signature name cells are merged to span columns A through D
        // This provides enough width for full names without breaking
        $mergeRange = 'A' . $row . ':D' . $row;
        
        // First, unmerge any existing merge for this cell
        $existingMerges = $sheet->getMergeCells();
        foreach ($existingMerges as $existingRange) {
            try {
                if ($sheet->getCell($cell)->isInRange($existingRange)) {
                    $sheet->unmergeCells($existingRange);
                    break;
                }
            } catch (\Exception $e) {
                // Ignore if cell is not in range
            }
        }
        
        // Merge the cells for the signature name
        try {
            $sheet->mergeCells($mergeRange);
        } catch (\Exception $e) {
            \Log::debug('Could not merge cells for signature name', ['range' => $mergeRange, 'error' => $e->getMessage()]);
        }
        
        // Set the value - use setValueExplicit to prevent truncation
        $sheet->setCellValueExplicit($cell, $name, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        
        // Get the style from template cell (A132 is the first name cell in template)
        try {
            $templateCell = 'A132';
            $templateStyle = $sheet->getStyle($templateCell);
            $templateFont = $templateStyle->getFont();
            
            // Use provided font size or get from template
            $finalFontSize = $fontSize > 0 ? $fontSize : ($templateFont->getSize() ?: 12);
            
            // Apply template style but force bold and ensure proper size
            $sheet->getStyle($cell)->getFont()->applyFromArray([
                'name' => $templateFont->getName() ?: 'Calibri',
                'size' => $finalFontSize,
                'bold' => true, // Names are BOLD
            ]);
            
            // IMPORTANT: Disable text wrapping to prevent names from breaking
            // The merged cells should be wide enough to accommodate full names
            $sheet->getStyle($cell)->getAlignment()->setWrapText(false);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle($cell)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM);
            
            // Add bottom border to the merged range
            if ($addBorder) {
                $sheet->getStyle($mergeRange)->getBorders()->getBottom()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
            }
        } catch (\Exception $e) {
            // Fallback: apply bold, proper size, and conditionally add bottom border
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFont()->setSize($fontSize);
            $sheet->getStyle($cell)->getAlignment()->setWrapText(false);
            if ($addBorder) {
                $sheet->getStyle($mergeRange)->getBorders()->getBottom()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        }
    }

    /**
     * Set signature title with template-matching formatting
     * Matches template style: regular font (not bold, not uppercase)
     */
    protected function setSignatureTitle($sheet, $cell, $title, $fontSize = 12)
    {
        // Extract row number from cell reference (e.g., 'A133' -> 133)
        $row = (int) filter_var($cell, FILTER_SANITIZE_NUMBER_INT);
        
        // Ensure signature title cells are merged to span columns A through D
        // This provides enough width for full titles without breaking
        $mergeRange = 'A' . $row . ':D' . $row;
        
        // First, unmerge any existing merge for this cell
        $existingMerges = $sheet->getMergeCells();
        foreach ($existingMerges as $existingRange) {
            try {
                if ($sheet->getCell($cell)->isInRange($existingRange)) {
                    $sheet->unmergeCells($existingRange);
                    break;
                }
            } catch (\Exception $e) {
                // Ignore if cell is not in range
            }
        }
        
        // Merge the cells for the signature title
        try {
            $sheet->mergeCells($mergeRange);
        } catch (\Exception $e) {
            \Log::debug('Could not merge cells for signature title', ['range' => $mergeRange, 'error' => $e->getMessage()]);
        }
        
        // Set the value (titles keep original case) - use setValueExplicit to prevent truncation
        $sheet->setCellValueExplicit($cell, trim($title), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        
        // Get the style from template cell (A133 is the first title cell in template)
        try {
            $templateCell = 'A133';
            $templateStyle = $sheet->getStyle($templateCell);
            $templateFont = $templateStyle->getFont();
            
            // Use provided font size or get from template
            $finalFontSize = $fontSize > 0 ? $fontSize : ($templateFont->getSize() ?: 12);
            
            // Copy all font properties from template (including not bold)
            $sheet->getStyle($cell)->getFont()->applyFromArray([
                'name' => $templateFont->getName() ?: 'Calibri',
                'size' => $finalFontSize,
                'bold' => $templateFont->getBold() ?? false, // Usually false for titles
            ]);
            
            // IMPORTANT: Disable text wrapping to prevent titles from breaking
            // The merged cells should be wide enough to accommodate full titles
            $sheet->getStyle($cell)->getAlignment()->setWrapText(false);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle($cell)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        } catch (\Exception $e) {
            // Fallback: regular font, not bold, proper size
            $sheet->getStyle($cell)->getFont()->setBold(false);
            $sheet->getStyle($cell)->getFont()->setSize($fontSize);
            $sheet->getStyle($cell)->getAlignment()->setWrapText(false);
        }
        
        // NOTE: Column width is preserved from template - signatures use merged cells
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
            // Calculate overall rating using weighted averages
            // Formula: UEE + (GWA × 0.3) + (Interview × 0.05) + (SkillTest × 0.05)
            // Note: UEE is already weighted (0-60), so use as-is
            
            // Get UEE (already weighted 0-60)
            $ueeWeighted = (float) ($applicant->score ?? 0);
            
            // Get GWA, Interview, and SkillTest (raw percentages)
            $gwaRaw = (float) ($applicant->card_tor_gwa ?? 0);
            $skillTestRaw = (float) ($applicant->enrollassess_score ?? 0);
            $interviewRaw = (float) ($applicant->interview_score ?? 0);
            
            // Calculate weighted components
            $gwaWeighted = $gwaRaw * 0.30;
            $interviewWeighted = $interviewRaw * 0.05;
            $skillTestWeighted = $skillTestRaw * 0.05;
            $interviewSkillWeighted = $interviewWeighted + $skillTestWeighted; // Combined 10%
            
            // Calculate overall rating
            $overall = 0.0;
            
            if ($this->scoringService->hasAllRequiredScores($applicant)) {
                // Use the scoring service for consistent calculation
                $rating = $this->scoringService->calculateOverallRating($applicant);
                $overall = $rating['overall_rating'];
            } else {
                // Calculate partial score if some components are missing
                $overall = $ueeWeighted + $gwaWeighted + $interviewWeighted + $skillTestWeighted;
            }

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
            // Application number and phone number must be set as STRING to prevent Excel from converting to scientific notation
            $sheet->setCellValueExplicit('B' . $currentRow, (string)($applicant->application_no ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $currentRow, $applicant->preferred_course ?? 'BSIT');
            $sheet->setCellValue('D' . $currentRow, strtoupper($applicant->last_name));
            $sheet->setCellValue('E' . $currentRow, strtoupper($applicant->first_name));
            $sheet->setCellValue('F' . $currentRow, strtoupper($applicant->middle_name ?? ''));
            $sheet->setCellValue('G' . $currentRow, $applicant->email_address);
            // Phone number must be set as STRING to prevent Excel from converting to scientific notation (e.g., 6.39021E+11)
            $phoneNumber = (string)($applicant->phone_number ?? '');
            $sheet->setCellValueExplicit('H' . $currentRow, $phoneNumber, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            // Ensure phone number cell displays fully without wrapping or truncation
            $sheet->getStyle('H' . $currentRow)->getAlignment()->setWrapText(false);
            $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('I' . $currentRow, number_format($ueeWeighted, 2));
            $sheet->setCellValue('J' . $currentRow, number_format($gwaWeighted, 2));
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

