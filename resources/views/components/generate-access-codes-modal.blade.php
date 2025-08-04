<!-- Generate Access Codes Modal Component -->
<div id="generateCodesModal" class="modal-overlay access-codes-modal-overlay" style="display: none;">
    <div class="modal-content access-codes-modal-content">
        <!-- Modal Header -->
        <div class="modal-header access-codes-header">
            <div class="header-icon">
                <div class="icon-circle">
                    <span class="icon-symbol">🔑</span>
                </div>
            </div>
            <h3 class="modal-title">Generate New Access Codes</h3>
            <p class="modal-subtitle">Create unique access codes for new batch of applicants</p>
            <button onclick="closeCodesModal()" class="modal-close">×</button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body access-codes-body" id="codesModalBody">
            <!-- Generation Form -->
            <div class="generation-form" id="generationForm">
                <div class="form-section">
                    <div class="form-group">
                        <label for="codeCount" class="form-label">Number of codes to generate</label>
                        <div class="input-with-controls">
                            <button type="button" onclick="adjustCodeCount(-1)" class="count-btn count-decrease">−</button>
                            <input type="number" 
                                   id="codeCount" 
                                   name="codeCount" 
                                   class="form-control count-input"
                                   min="1" 
                                   max="100" 
                                   value="10"
                                   placeholder="10">
                            <button type="button" onclick="adjustCodeCount(1)" class="count-btn count-increase">+</button>
                        </div>
                        <div class="form-help">Minimum: 1, Maximum: 100 codes per batch</div>
                    </div>

                    <div class="form-group">
                        <label for="codePrefix" class="form-label">Code Prefix (Optional)</label>
                        <input type="text" 
                               id="codePrefix" 
                               name="codePrefix" 
                               class="form-control"
                               placeholder="e.g., BSIT2024"
                               maxlength="10">
                        <div class="form-help">Adds a prefix to all generated codes (max 10 characters)</div>
                    </div>

                    <div class="form-group">
                        <label for="codeLength" class="form-label">Code Length</label>
                        <select id="codeLength" name="codeLength" class="form-select">
                            <option value="6">6 characters</option>
                            <option value="8" selected>8 characters</option>
                            <option value="10">10 characters</option>
                            <option value="12">12 characters</option>
                        </select>
                        <div class="form-help">Length of the unique code portion (excluding prefix)</div>
                    </div>

                    <div class="generation-preview">
                        <div class="preview-label">Preview:</div>
                        <div class="preview-code" id="previewCode">BSIT2024-ABC123XY</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="closeCodesModal()" class="btn-cancel">
                        Cancel
                    </button>
                    <button type="button" onclick="generateCodes()" class="btn-generate" id="generateBtn">
                        <span class="btn-icon">🔑</span>
                        <span class="btn-text">Generate Codes</span>
                    </button>
                </div>
            </div>

            <!-- Results Section -->
            <div class="codes-results" id="codesResults" style="display: none;">
                <div class="results-header">
                    <div class="success-icon">✅</div>
                    <h4 class="results-title">Access Codes Generated Successfully!</h4>
                    <p class="results-subtitle"><span id="generatedCount">10</span> unique access codes have been created</p>
                </div>

                <div class="codes-container">
                    <div class="codes-header">
                        <div class="codes-title">Generated Access Codes</div>
                        <div class="codes-actions">
                            <button onclick="selectAllCodes()" class="action-btn">📋 Select All</button>
                            <button onclick="copyCodes()" class="action-btn">📄 Copy All</button>
                            <button onclick="exportCodes()" class="action-btn">📊 Export CSV</button>
                        </div>
                    </div>
                    
                    <div class="codes-list" id="codesList">
                        <!-- Generated codes will be inserted here -->
                    </div>
                </div>

                <div class="distribution-options">
                    <h5 class="distribution-title">Distribution Options</h5>
                    <div class="distribution-actions">
                        <button onclick="emailCodes()" class="btn-primary">
                            📧 Email to Applicants
                        </button>
                        <button onclick="printCodes()" class="btn-secondary">
                            🖨️ Print Codes
                        </button>
                        <button onclick="downloadPDF()" class="btn-secondary">
                            📄 Download PDF
                        </button>
                    </div>
                </div>

                <div class="results-actions">
                    <button type="button" onclick="generateMoreCodes()" class="btn-secondary">
                        ➕ Generate More Codes
                    </button>
                    <button type="button" onclick="closeCodesModal()" class="btn-primary">
                        ✅ Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Variables (ensure they're available) */
:root {
    --maroon-primary: #800020;
    --maroon-dark: #5c0017;
    --maroon-light: #a0002a;
    --yellow-primary: #FFD700;
    --yellow-dark: #E6C200;
    --yellow-light: #FFF8DC;
    --white: #FFFFFF;
    --light-gray: #F8F9FA;
    --border-gray: #E9ECEF;
    --text-gray: #6B7280;
    --transition: all 0.3s ease;
}

/* Enhanced Access Codes Modal Styles - Override any conflicting modal styles */
.modal-overlay.access-codes-modal-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: rgba(0, 0, 0, 0.6) !important;
    backdrop-filter: blur(4px);
    animation: modalFadeIn 0.3s ease-out;
    z-index: 9999 !important;
    align-items: center !important;
    justify-content: center !important;
}

/* When modal is visible, show as flex */
.modal-overlay.access-codes-modal-overlay[style*="display: flex"] {
    display: flex !important;
}

/* Ensure modal is hidden by default */
.modal-overlay.access-codes-modal-overlay[style*="display: none"] {
    display: none !important;
}

.access-codes-modal-content {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    border: 3px solid var(--yellow-primary);
    animation: modalSlideIn 0.3s ease-out;
}

.access-codes-header {
    background: linear-gradient(135deg, var(--yellow-light) 0%, var(--yellow-primary) 100%);
    padding: 30px;
    text-align: center;
    border-bottom: 2px solid var(--maroon-primary);
    position: relative;
}

.header-icon {
    margin-bottom: 16px;
}

.icon-circle {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 4px solid var(--white);
    box-shadow: 0 8px 20px rgba(128, 0, 32, 0.3);
}

.icon-symbol {
    font-size: 28px;
    color: var(--white);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.access-codes-header .modal-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--maroon-primary);
    margin: 0 0 8px 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.modal-subtitle {
    font-size: 14px;
    color: var(--maroon-dark);
    margin: 0;
    opacity: 0.9;
}

.access-codes-header .modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: var(--maroon-primary);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: var(--transition);
}

.access-codes-header .modal-close:hover {
    background: rgba(128, 0, 32, 0.1);
}

.access-codes-body {
    padding: 0;
    max-height: 60vh;
    overflow-y: auto;
}

/* Generation Form */
.generation-form {
    padding: 30px;
}

.form-section {
    display: grid;
    gap: 24px;
    margin-bottom: 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-weight: 600;
    color: var(--maroon-primary);
    font-size: 14px;
}

.input-with-controls {
    display: flex;
    align-items: center;
    border: 2px solid var(--border-gray);
    border-radius: 8px;
    overflow: hidden;
    transition: var(--transition);
}

.input-with-controls:focus-within {
    border-color: var(--yellow-primary);
    box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
}

.count-btn {
    width: 40px;
    height: 48px;
    background: var(--light-gray);
    border: none;
    cursor: pointer;
    font-size: 18px;
    font-weight: 700;
    color: var(--maroon-primary);
    transition: var(--transition);
}

.count-btn:hover {
    background: var(--yellow-light);
}

.count-input {
    flex: 1;
    border: none;
    padding: 12px 16px;
    font-size: 16px;
    text-align: center;
    font-weight: 600;
    background: var(--white);
}

.count-input:focus {
    outline: none;
}

.form-control, .form-select {
    padding: 12px 16px;
    border: 2px solid var(--border-gray);
    border-radius: 8px;
    font-size: 14px;
    transition: var(--transition);
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: var(--yellow-primary);
    box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
}

.form-help {
    font-size: 12px;
    color: var(--text-gray);
    font-style: italic;
}

.generation-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--yellow-light);
    border: 2px solid var(--yellow-primary);
    border-radius: 8px;
    margin-top: 8px;
}

.preview-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--maroon-primary);
}

.preview-code {
    font-family: 'Courier New', monospace;
    font-size: 16px;
    font-weight: 700;
    color: var(--maroon-primary);
    background: var(--white);
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid var(--border-gray);
    letter-spacing: 1px;
}

.form-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    padding-top: 20px;
    border-top: 1px solid var(--border-gray);
}

.btn-cancel, .btn-generate {
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
}

.btn-cancel {
    background: var(--white);
    color: var(--text-gray);
    border: 2px solid var(--border-gray);
}

.btn-cancel:hover {
    background: var(--border-gray);
    border-color: var(--text-gray);
}

.btn-generate {
    background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
    color: var(--white);
    border: 2px solid var(--maroon-primary);
}

.btn-generate:hover:not(:disabled) {
    background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
    color: var(--maroon-primary);
    border-color: var(--yellow-primary);
    transform: translateY(-1px);
}

.btn-generate:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-generate.loading .btn-icon {
    animation: spin 1s linear infinite;
}

/* Results Section */
.codes-results {
    padding: 30px;
}

.results-header {
    text-align: center;
    margin-bottom: 30px;
}

.success-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.results-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--maroon-primary);
    margin: 0 0 8px 0;
}

.results-subtitle {
    font-size: 14px;
    color: var(--text-gray);
    margin: 0;
}

.codes-container {
    border: 2px solid var(--border-gray);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
}

.codes-header {
    background: var(--light-gray);
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-gray);
}

.codes-title {
    font-weight: 600;
    color: var(--maroon-primary);
    font-size: 16px;
}

.codes-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    padding: 6px 12px;
    background: var(--white);
    border: 1px solid var(--border-gray);
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    color: var(--maroon-primary);
    transition: var(--transition);
}

.action-btn:hover {
    background: var(--yellow-light);
    border-color: var(--yellow-primary);
}

.codes-list {
    max-height: 200px;
    overflow-y: auto;
    padding: 16px 20px;
    background: var(--white);
}

.code-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    margin-bottom: 4px;
    background: var(--light-gray);
    border-radius: 6px;
    border: 1px solid var(--border-gray);
}

.code-value {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--maroon-primary);
    letter-spacing: 1px;
}

.code-copy-btn {
    padding: 4px 8px;
    background: var(--yellow-light);
    border: 1px solid var(--yellow-primary);
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
    color: var(--maroon-primary);
    transition: var(--transition);
}

.code-copy-btn:hover {
    background: var(--yellow-primary);
}

.distribution-options {
    margin-bottom: 30px;
}

.distribution-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--maroon-primary);
    margin: 0 0 16px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--yellow-primary);
}

.distribution-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.results-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    padding-top: 20px;
    border-top: 1px solid var(--border-gray);
}

.btn-primary, .btn-secondary {
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: var(--transition);
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
    color: var(--white);
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
    color: var(--maroon-primary);
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--white);
    color: var(--maroon-primary);
    border: 2px solid var(--border-gray);
}

.btn-secondary:hover {
    background: var(--yellow-light);
    border-color: var(--yellow-primary);
}

/* Responsive Design */
@media (max-width: 768px) {
    .access-codes-modal-content {
        margin: 20px;
        width: calc(100% - 40px);
        max-height: calc(100vh - 40px);
    }
    
    .access-codes-header {
        padding: 24px 20px;
    }
    
    .generation-form, .codes-results {
        padding: 24px 20px;
    }
    
    .codes-header {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .codes-actions {
        justify-content: center;
    }
    
    .distribution-actions {
        flex-direction: column;
    }
    
    .form-actions, .results-actions {
        flex-direction: column;
    }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Generate Access Codes Modal JavaScript
let generatedCodes = [];

/**
 * Show the generate access codes modal
 */
function showGenerateCodesModal() {
    document.getElementById('generateCodesModal').style.display = 'flex';
    updatePreview();
    
    // Focus the count input
    setTimeout(() => {
        document.getElementById('codeCount').focus();
    }, 100);
}

/**
 * Close the generate access codes modal
 */
function closeCodesModal() {
    document.getElementById('generateCodesModal').style.display = 'none';
    
    // Reset to generation form
    document.getElementById('generationForm').style.display = 'block';
    document.getElementById('codesResults').style.display = 'none';
    
    // Reset form
    document.getElementById('codeCount').value = 10;
    document.getElementById('codePrefix').value = '';
    document.getElementById('codeLength').value = 8;
    updatePreview();
    
    // Clear generated codes
    generatedCodes = [];
}

/**
 * Adjust the code count with buttons
 */
function adjustCodeCount(delta) {
    const input = document.getElementById('codeCount');
    const currentValue = parseInt(input.value) || 10;
    const newValue = Math.max(1, Math.min(100, currentValue + delta));
    input.value = newValue;
    updatePreview();
}

/**
 * Update the preview code
 */
function updatePreview() {
    const prefix = document.getElementById('codePrefix').value || 'BSIT2024';
    const length = parseInt(document.getElementById('codeLength').value) || 8;
    
    // Generate sample code
    const sampleCode = generateSampleCode(prefix, length);
    document.getElementById('previewCode').textContent = sampleCode;
}

/**
 * Generate a sample code for preview
 */
function generateSampleCode(prefix, length) {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    
    for (let i = 0; i < length; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    
    return prefix ? `${prefix}-${code}` : code;
}

/**
 * Generate the access codes
 */
function generateCodes() {
    const count = parseInt(document.getElementById('codeCount').value) || 10;
    const prefix = document.getElementById('codePrefix').value;
    const length = parseInt(document.getElementById('codeLength').value) || 8;
    
    if (count < 1 || count > 100) {
        alert('Please enter a number between 1 and 100.');
        return;
    }
    
    const generateBtn = document.getElementById('generateBtn');
    const btnIcon = generateBtn.querySelector('.btn-icon');
    const btnText = generateBtn.querySelector('.btn-text');
    
    // Show loading state
    generateBtn.disabled = true;
    generateBtn.classList.add('loading');
    btnText.textContent = 'Generating...';
    
    // Simulate generation process
    setTimeout(() => {
        generatedCodes = [];
        const usedCodes = new Set();
        
        // Generate unique codes
        while (generatedCodes.length < count) {
            const code = generateUniqueCode(prefix, length, usedCodes);
            if (code && !usedCodes.has(code)) {
                generatedCodes.push(code);
                usedCodes.add(code);
            }
        }
        
        // Display results
        displayGeneratedCodes();
        
        // Switch to results view
        document.getElementById('generationForm').style.display = 'none';
        document.getElementById('codesResults').style.display = 'block';
        
        // Reset button state
        generateBtn.disabled = false;
        generateBtn.classList.remove('loading');
        btnText.textContent = 'Generate Codes';
        
    }, 1500); // Simulate loading time
}

/**
 * Generate a unique access code
 */
function generateUniqueCode(prefix, length, usedCodes) {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let attempts = 0;
    
    while (attempts < 100) { // Prevent infinite loop
        let code = '';
        
        for (let i = 0; i < length; i++) {
            code += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        
        const fullCode = prefix ? `${prefix}-${code}` : code;
        
        if (!usedCodes.has(fullCode)) {
            return fullCode;
        }
        
        attempts++;
    }
    
    return null;
}

/**
 * Display the generated codes
 */
function displayGeneratedCodes() {
    const codesList = document.getElementById('codesList');
    const generatedCount = document.getElementById('generatedCount');
    
    generatedCount.textContent = generatedCodes.length;
    
    codesList.innerHTML = generatedCodes.map((code, index) => `
        <div class="code-item">
            <span class="code-value">${code}</span>
            <button onclick="copyCode('${code}')" class="code-copy-btn">📋 Copy</button>
        </div>
    `).join('');
}

/**
 * Copy a single code
 */
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        // Show temporary success message
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = '✅ Copied';
        btn.style.background = '#dcfce7';
        
        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = '';
        }, 1000);
    }).catch(() => {
        alert('Failed to copy code. Please select and copy manually.');
    });
}

/**
 * Select all codes in the list
 */
function selectAllCodes() {
    const codesList = document.getElementById('codesList');
    const range = document.createRange();
    range.selectNodeContents(codesList);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
}

/**
 * Copy all codes to clipboard
 */
function copyCodes() {
    const codesText = generatedCodes.join('\n');
    navigator.clipboard.writeText(codesText).then(() => {
        alert(`${generatedCodes.length} access codes copied to clipboard!`);
    }).catch(() => {
        alert('Failed to copy codes. Please select and copy manually.');
    });
}

/**
 * Export codes as CSV
 */
function exportCodes() {
    const csvContent = 'Access Code\n' + generatedCodes.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `access_codes_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

/**
 * Email codes to applicants
 */
function emailCodes() {
    alert(`Email distribution feature would send ${generatedCodes.length} codes to applicants. (Demo mode)`);
}

/**
 * Print codes
 */
function printCodes() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Access Codes</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .code { font-family: monospace; font-size: 18px; margin: 10px 0; padding: 10px; border: 1px solid #ddd; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>BSIT Entrance Examination</h1>
                <h2>Access Codes</h2>
                <p>Generated on ${new Date().toLocaleDateString()}</p>
            </div>
            ${generatedCodes.map(code => `<div class="code">${code}</div>`).join('')}
            <div class="footer">
                <p>Computer Studies Department</p>
                <p>Total codes: ${generatedCodes.length}</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

/**
 * Download codes as PDF
 */
function downloadPDF() {
    alert('PDF download feature would generate a formatted PDF with all codes. (Demo mode)');
}

/**
 * Generate more codes
 */
function generateMoreCodes() {
    document.getElementById('generationForm').style.display = 'block';
    document.getElementById('codesResults').style.display = 'none';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Check if elements exist before adding listeners
    const elements = ['codeCount', 'codePrefix', 'codeLength'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updatePreview);
        }
    });
    
    // Validate code count input
    const codeCountInput = document.getElementById('codeCount');
    if (codeCountInput) {
        codeCountInput.addEventListener('input', function(e) {
            const value = parseInt(e.target.value);
            if (value < 1) e.target.value = 1;
            if (value > 100) e.target.value = 100;
        });
    }
    
    // Validate prefix input
    const codePrefixInput = document.getElementById('codePrefix');
    if (codePrefixInput) {
        codePrefixInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
});

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'generateCodesModal') {
        closeCodesModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('generateCodesModal').style.display === 'flex') {
        closeCodesModal();
    }
});

// Prevent closing when clicking inside the modal
document.addEventListener('click', function(e) {
    if (e.target && e.target.closest('.access-codes-modal-content')) {
        e.stopPropagation();
    }
});
</script>