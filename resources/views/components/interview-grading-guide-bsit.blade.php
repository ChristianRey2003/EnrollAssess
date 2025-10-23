{{-- Official BSIT Admission Interview Rubric Grading Guide --}}
<div class="grading-guide-panel">
    <div class="guide-header" onclick="toggleGuide(this)">
        <h5 class="guide-title">BSIT Admission Interview Rubric</h5>
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
                    <span class="scale-value">10</span>
                    <span class="scale-label">Excellent</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">8</span>
                    <span class="scale-label">Good</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">6</span>
                    <span class="scale-label">Fair</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">4</span>
                    <span class="scale-label">Needs Improvement</span>
                </div>
                <div class="scale-item">
                    <span class="scale-value">0-2</span>
                    <span class="scale-label">Insufficient</span>
                </div>
            </div>
        </div>

        {{-- Evaluation Criteria --}}
        <div class="guide-section">
            <h6 class="section-title">Evaluation Criteria</h6>
            <div class="criteria-list">
                <div class="criteria-row">
                    <span class="criteria-name">Communication Skills</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
                <div class="criteria-row">
                    <span class="criteria-name">Motivation and Interest</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
                <div class="criteria-row">
                    <span class="criteria-name">Problem-solving Attitude</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
                <div class="criteria-row">
                    <span class="criteria-name">Understanding of Program</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
                <div class="criteria-row">
                    <span class="criteria-name">Personality and Attitude</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
                <div class="criteria-row">
                    <span class="criteria-name">IT Exposure / Background</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
                <div class="criteria-row">
                    <span class="criteria-name">Willingness to Learn</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
                <div class="criteria-row">
                    <span class="criteria-name">Overall Impression</span>
                    <span class="criteria-weight">10 pts</span>
                </div>
            </div>
            <div class="total-score">
                <strong>Total Score: 100 points</strong>
                <span class="passing-note">Passing: 50 points</span>
            </div>
        </div>

        {{-- Recommendations --}}
        <div class="guide-section">
            <h6 class="section-title">Recommendation Scoring</h6>
            <div class="recommendations-list">
                <div class="recommendation-row">
                    <span class="rec-label">Highly Recommended</span>
                    <span class="rec-score">20 pts</span>
                </div>
                <div class="recommendation-row">
                    <span class="rec-label">Recommended</span>
                    <span class="rec-score">10 pts</span>
                </div>
                <div class="recommendation-row">
                    <span class="rec-label">Conditional</span>
                    <span class="rec-score">5 pts</span>
                </div>
                <div class="recommendation-row">
                    <span class="rec-label">Not Recommended</span>
                    <span class="rec-score">0 pts</span>
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
    grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
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
    font-size: 0.7rem;
    color: #666;
}

.criteria-list,
.recommendations-list {
    background: white;
    border: 1px solid #E5E5E5;
    border-radius: 4px;
    overflow: hidden;
}

.criteria-row,
.recommendation-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid #F5F5F5;
}

.criteria-row:last-child,
.recommendation-row:last-child {
    border-bottom: none;
}

.criteria-name,
.rec-label {
    font-size: 0.8rem;
    color: #333;
}

.criteria-weight {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--maroon-primary, #800020);
}

.total-score {
    margin-top: 12px;
    padding: 10px 12px;
    background: #FFF9E6;
    border: 1px solid #FFE082;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-score strong {
    font-size: 0.85rem;
    color: #333;
}

.passing-note {
    font-size: 0.75rem;
    color: #666;
    font-weight: 600;
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

