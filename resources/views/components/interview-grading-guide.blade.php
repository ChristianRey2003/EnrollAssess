{{-- Minimalist Professional Grading Guide Component --}}
<div class="grading-guide-panel">
    <div class="guide-header" onclick="toggleGuide(this)">
        <h5 class="guide-title">Evaluation Rubric</h5>
        <svg class="guide-toggle-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </div>
    
    <div class="guide-content">
        {{-- Score Scale --}}
        <div class="guide-section">
            <h6 class="section-title">Score Scale (per criterion)</h6>
            <div class="scale-grid">
                <div class="scale-item">
                    <span class="scale-value">0-2</span>
                    <span class="scale-label">Insufficient</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">3-4</span>
                    <span class="scale-label">Basic</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">5-6</span>
                    <span class="scale-label">Competent</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">7-8</span>
                    <span class="scale-label">Proficient</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">9-10</span>
                    <span class="scale-label">Excellent</span>
                </div>
            </div>
        </div>

        {{-- Categories --}}
        <div class="guide-section">
            <h6 class="section-title">Evaluation Categories</h6>
            <div class="categories-list">
                <div class="category-row">
                    <strong class="category-name">Technical Skills</strong>
                    <span class="category-weight">40 pts</span>
                </div>
                <div class="category-row">
                    <strong class="category-name">Communication</strong>
                    <span class="category-weight">30 pts</span>
                </div>
                <div class="category-row">
                    <strong class="category-name">Analytical Thinking</strong>
                    <span class="category-weight">30 pts</span>
                </div>
            </div>
        </div>

        {{-- Recommendations --}}
        <div class="guide-section">
            <h6 class="section-title">Recommendation Guidelines</h6>
            <div class="recommendations-list">
                <div class="recommendation-row">
                    <span class="rec-label">Highly Recommended</span>
                    <span class="rec-score">85-100</span>
                </div>
                <div class="recommendation-row">
                    <span class="rec-label">Recommended</span>
                    <span class="rec-score">70-84</span>
                </div>
                <div class="recommendation-row">
                    <span class="rec-label">Conditional</span>
                    <span class="rec-score">50-69</span>
                </div>
                <div class="recommendation-row">
                    <span class="rec-label">Not Recommended</span>
                    <span class="rec-score">0-49</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.grading-guide-panel {
    background: #FAFAFA;
    border: 1px solid #E5E5E5;
    border-radius: 6px;
    overflow: hidden;
}

.guide-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #F5F5F5;
    border-bottom: 1px solid #E5E5E5;
    cursor: pointer;
    user-select: none;
    transition: background 0.2s;
}

.guide-header:hover {
    background: #EFEFEF;
}

.guide-title {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
    letter-spacing: -0.01em;
}

.guide-toggle-icon {
    color: #666;
    transition: transform 0.3s;
}

.guide-header.collapsed .guide-toggle-icon {
    transform: rotate(-90deg);
}

.guide-content {
    padding: 16px;
    max-height: 500px;
    overflow-y: auto;
    transition: all 0.3s;
}

.guide-header.collapsed + .guide-content {
    display: none;
}

.guide-section {
    margin-bottom: 20px;
}

.guide-section:last-child {
    margin-bottom: 0;
}

.section-title {
    margin: 0 0 10px 0;
    font-size: 0.8rem;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.scale-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 8px;
}

.scale-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 6px;
    background: white;
    border: 1px solid #E5E5E5;
    border-radius: 4px;
    text-align: center;
}

.scale-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.scale-label {
    font-size: 0.75rem;
    color: #666;
}

.categories-list,
.recommendations-list {
    background: white;
    border: 1px solid #E5E5E5;
    border-radius: 4px;
    overflow: hidden;
}

.category-row,
.recommendation-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-bottom: 1px solid #F5F5F5;
}

.category-row:last-child,
.recommendation-row:last-child {
    border-bottom: none;
}

.category-name,
.rec-label {
    font-size: 0.85rem;
    color: #333;
}

.category-weight {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--maroon-primary, #800020);
}

.rec-score {
    font-size: 0.75rem;
    font-weight: 600;
    color: #666;
    background: #F5F5F5;
    padding: 2px 8px;
    border-radius: 3px;
}

@media (max-width: 768px) {
    .scale-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function toggleGuide(header) {
    header.classList.toggle('collapsed');
}
</script>

