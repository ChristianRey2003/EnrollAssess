# LibreOffice PDF Export Setup

## What Changed
The PDF export now uses **LibreOffice headless mode** instead of PhpSpreadsheet's mPDF writer. This provides Excel-compatible rendering that matches your Google Sheets/Excel print output exactly.

## How It Works
1. Fill the Excel template with applicant data (same as before)
2. Configure page setup: Legal, Landscape, 130% scale, Narrow margins, exact column widths
3. Save as XLSX to a temp file
4. Convert XLSX → PDF using LibreOffice headless: `soffice --headless --convert-to pdf`
5. Return the generated PDF

## Installation

### Windows (Laragon)
1. Download LibreOffice: https://www.libreoffice.org/download/download/
2. Install to default location: `C:\Program Files\LibreOffice\`
3. The code will auto-detect it

**Manual path (optional):**
Add to `.env`:
```
LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"
```

### Linux (Production)
```bash
sudo apt update
sudo apt install libreoffice-calc libreoffice-writer --no-install-recommends -y
```

### macOS
```bash
brew install --cask libreoffice
```

## Testing
After installing LibreOffice, generate a PDF report:
1. Go to Reports page
2. Select "EVSU Entrance Results (PDF)"
3. Choose filters and generate
4. The PDF should now match your Excel/Google Sheets print layout exactly

## Troubleshooting

### Error: "LibreOffice not found"
- **Windows**: Install LibreOffice and restart your terminal/IDE
- **Linux**: Run `which soffice` to verify installation
- **Custom path**: Set `LIBREOFFICE_PATH` in `.env`

### PDF not generated
- Check `storage/logs/laravel.log` for LibreOffice errors
- Ensure LibreOffice can run headless: test manually:
  ```bash
  soffice --headless --convert-to pdf test.xlsx
  ```

### Permissions error
- **Linux**: Ensure web server user can execute soffice
- **Windows**: Run Laravel dev server as Administrator (if needed)

## Benefits
- ✅ **Exact Excel rendering**: Matches your Excel/Google Sheets output 1:1
- ✅ **Reliable page layout**: Legal, Landscape, 130% scale, Narrow margins
- ✅ **No PhpSpreadsheet quirks**: Uses native Excel print engine
- ✅ **Works offline**: No external APIs needed

## Fallback (if LibreOffice not available)
The code will throw a clear error with installation instructions. XLSX export continues to work normally.

