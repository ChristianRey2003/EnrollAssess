<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DiagnosePdfExport extends Command
{
    protected $signature = 'pdf:diagnose';
    protected $description = 'Diagnose PDF export column widths and page setup';

    public function handle()
    {
        $templatePath = resource_path('reports/templates/NEW TEMPLATE.xlsx');
        
        if (!file_exists($templatePath)) {
            $this->error("Template not found: {$templatePath}");
            return 1;
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $this->info("=== TEMPLATE DIAGNOSIS ===");
        $this->newLine();

        // Check column widths
        $this->info("Current Column Widths (A-L):");
        $totalWidth = 0;
        for ($col = 'A'; $col <= 'L'; $col++) {
            $width = $sheet->getColumnDimension($col)->getWidth();
            if ($width == -1) {
                $width = $sheet->getDefaultColumnDimension()->getWidth();
            }
            $totalWidth += $width;
            $this->line("  Column {$col}: {$width} (Excel units)");
        }
        $this->info("Total width: {$totalWidth} Excel units");
        $this->newLine();

        // Check page setup
        $pageSetup = $sheet->getPageSetup();
        $margins = $sheet->getPageMargins();
        
        $this->info("Current Page Setup:");
        $this->line("  Orientation: " . $pageSetup->getOrientation());
        $this->line("  Paper Size: " . $pageSetup->getPaperSize());
        $this->line("  Fit To Width: " . ($pageSetup->getFitToWidth() ?: 'Not set'));
        $this->line("  Fit To Height: " . ($pageSetup->getFitToHeight() ?: 'Not set'));
        $this->line("  Horizontal Centered: " . ($pageSetup->getHorizontalCentered() ? 'Yes' : 'No'));
        $this->newLine();

        $this->info("Current Margins (inches):");
        $this->line("  Top: " . $margins->getTop());
        $this->line("  Bottom: " . $margins->getBottom());
        $this->line("  Left: " . $margins->getLeft());
        $this->line("  Right: " . $margins->getRight());
        $this->line("  Header: " . $margins->getHeader());
        $this->line("  Footer: " . $margins->getFooter());
        $this->newLine();

        // Calculate optimal widths for Legal Landscape
        $this->info("=== OPTIMAL WIDTHS FOR LEGAL LANDSCAPE ===");
        $legalWidthInches = 14; // Legal paper width
        $marginLeft = 0.25;
        $marginRight = 0.25;
        $printableWidth = $legalWidthInches - $marginLeft - $marginRight; // 13.5 inches
        
        // Excel column width: 1 unit ≈ 0.14 inches (for default font)
        // But this varies, so we'll calculate based on actual printable area
        $excelUnitsPerInch = 7.43; // Approximate conversion
        $targetTotalUnits = $printableWidth * $excelUnitsPerInch; // ~100 units
        
        $this->line("Legal Landscape (14\"):");
        $this->line("  Printable width: {$printableWidth}\" (with 0.25\" margins)");
        $this->line("  Target total column width: ~{$targetTotalUnits} Excel units");
        $this->line("  Current total: {$totalWidth} Excel units");
        $this->line("  Difference: " . ($targetTotalUnits - $totalWidth) . " units");
        $this->newLine();

        // Suggest column widths
        $this->info("Suggested Column Widths for PDF (to fill Legal landscape):");
        $suggestedWidths = [
            'A' => 6,   // No.
            'B' => 14,  // Application No.
            'C' => 12,  // Preferred Program
            'D' => 18,  // Last Name
            'E' => 18,  // First Name
            'F' => 14,  // Middle Name
            'G' => 30,  // E-mail
            'H' => 18,  // Contact Number
            'I' => 22,  // UEE (60%)
            'J' => 18,  // Card/TOR GWA (30%)
            'K' => 22,  // Interview/Skill Test (10%)
            'L' => 20,  // Overall Rating
        ];
        
        $suggestedTotal = 0;
        foreach ($suggestedWidths as $col => $width) {
            $suggestedTotal += $width;
            $this->line("  Column {$col}: {$width} units");
        }
        $this->info("Suggested total: {$suggestedTotal} Excel units");
        $this->newLine();

        return 0;
    }
}

