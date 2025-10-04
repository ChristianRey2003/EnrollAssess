{{-- Grading Guide Panel --}}
<div class="grading-guide">
    <div class="guide-header">
        <h3 class="guide-title">
            <span class="guide-icon">📋</span>
            Grading Guide
        </h3>
        <button type="button" class="guide-toggle" onclick="toggleGradingGuide()">
            <span class="toggle-text">Hide</span>
            <span class="toggle-icon">▼</span>
        </button>
    </div>

    <div class="guide-content" id="grading-guide-content">
        {{-- Scoring Scale --}}
        <div class="scoring-scale">
            <h4>Scoring Scale (1-5 Points)</h4>
            <div class="scale-items">
                @foreach(config('interview_rubric.scoring_scale', []) as $score => $details)
                    <div class="scale-item">
                        <div class="score-badge score-{{ $score }}">{{ $score }}</div>
                        <div class="score-details">
                            <div class="score-label">{{ $details['label'] }}</div>
                            <div class="score-description">{{ $details['description'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Evaluation Categories --}}
        <div class="evaluation-categories">
            <h4>Evaluation Categories</h4>
            @foreach(config('interview_rubric.evaluation_categories', []) as $categoryKey => $category)
                <div class="category-card">
                    <div class="category-header">
                        <h5>{{ $category['name'] }}</h5>
                        <span class="category-weight">{{ $category['weight'] }}% Weight</span>
                    </div>
                    <p class="category-description">{{ $category['description'] }}</p>
                    <div class="category-criteria">
                        <strong>Criteria:</strong>
                        <ul>
                            @foreach($category['criteria'] as $criteriaKey => $criteriaName)
                                <li>{{ $criteriaName }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Recommendation Guidelines --}}
        <div class="recommendation-guidelines">
            <h4>Recommendation Guidelines</h4>
            <div class="recommendation-items">
                @foreach(config('interview_rubric.recommendation_levels', []) as $level => $details)
                    <div class="recommendation-item">
                        <div class="recommendation-label">{{ $details['label'] }}</div>
                        <div class="recommendation-description">{{ $details['description'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Tips --}}
        <div class="quick-tips">
            <h4>Quick Tips</h4>
            <ul class="tips-list">
                <li>Consider the candidate's background and experience level</li>
                <li>Focus on problem-solving approach, not just correct answers</li>
                <li>Look for communication clarity and thought process</li>
                <li>Be consistent in your scoring across all candidates</li>
                <li>Use the full scale - don't cluster scores in the middle</li>
            </ul>
        </div>
    </div>
</div>

<style>
.grading-guide {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 2rem;
    overflow: hidden;
}

.guide-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: #4f46e5;
    color: white;
}

.guide-title {
    margin: 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.guide-icon {
    font-size: 1.2rem;
}

.guide-toggle {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.guide-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
}

.guide-content {
    padding: 1.25rem;
    max-height: 70vh;
    overflow-y: auto;
}

.guide-content.hidden {
    display: none;
}

.scoring-scale {
    margin-bottom: 2rem;
}

.scoring-scale h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
}

.scale-items {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.scale-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.score-badge {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.9rem;
    color: white;
}

.score-1 { background: #dc2626; }
.score-2 { background: #f59e0b; }
.score-3 { background: #3b82f6; }
.score-4 { background: #10b981; }
.score-5 { background: #059669; }

.score-details {
    flex: 1;
}

.score-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}

.score-description {
    font-size: 0.9rem;
    color: #6b7280;
}

.evaluation-categories {
    margin-bottom: 2rem;
}

.evaluation-categories h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
}

.category-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.category-header h5 {
    margin: 0;
    color: #374151;
    font-size: 0.95rem;
}

.category-weight {
    background: #ddd6fe;
    color: #5b21b6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.category-description {
    margin: 0 0 0.75rem 0;
    color: #6b7280;
    font-size: 0.9rem;
}

.category-criteria {
    font-size: 0.9rem;
}

.category-criteria ul {
    margin: 0.5rem 0 0 0;
    padding-left: 1.5rem;
}

.category-criteria li {
    margin-bottom: 0.25rem;
    color: #6b7280;
}

.recommendation-guidelines h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
}

.recommendation-items {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.recommendation-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 0.75rem;
}

.recommendation-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}

.recommendation-description {
    font-size: 0.9rem;
    color: #6b7280;
}

.quick-tips {
    margin-top: 2rem;
}

.quick-tips h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
}

.tips-list {
    margin: 0;
    padding-left: 1.5rem;
}

.tips-list li {
    margin-bottom: 0.5rem;
    color: #6b7280;
    font-size: 0.9rem;
}
</style>

<script>
function toggleGradingGuide() {
    const content = document.getElementById('grading-guide-content');
    const toggleText = document.querySelector('.guide-toggle .toggle-text');
    const toggleIcon = document.querySelector('.guide-toggle .toggle-icon');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        toggleText.textContent = 'Hide';
        toggleIcon.textContent = '▼';
    } else {
        content.classList.add('hidden');
        toggleText.textContent = 'Show';
        toggleIcon.textContent = '▶';
    }
}
</script>
