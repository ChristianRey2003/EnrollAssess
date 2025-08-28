<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Analytics - EnrollAssess Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="page-title">📈 Interview Analytics</h1>
                    <p class="page-subtitle">Interview performance insights and trends</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('admin.interviews.index') }}" class="btn-secondary">← Back to Interviews</a>
                    <button onclick="generateReport()" class="btn-primary">📊 Generate Report</button>
                </div>
            </div>
        </header>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ round(($analytics['completion_rate']['completed'] / max(1, array_sum($analytics['completion_rate']))) * 100, 1) }}%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-content">
                    <div class="stat-value">{{ round($analytics['average_ratings']['overall'] ?? 0, 1) }}</div>
                    <div class="stat-label">Average Overall Score</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-value">{{ $analytics['instructor_performance']->count() }}</div>
                    <div class="stat-label">Active Instructors</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ $analytics['recent_trends']->sum('count') }}</div>
                    <div class="stat-label">Recent Interviews (30 days)</div>
                </div>
            </div>
        </div>

        <div class="analytics-grid">
            <!-- Completion Rate Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Interview Status Distribution</h3>
                    <p>Breakdown of interview completion status</p>
                </div>
                <div class="chart-container">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>

            <!-- Average Ratings Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Average Rating Categories</h3>
                    <p>Performance across different evaluation criteria</p>
                </div>
                <div class="chart-container">
                    <canvas id="ratingsChart"></canvas>
                </div>
            </div>

            <!-- Instructor Performance -->
            <div class="chart-card full-width">
                <div class="chart-header">
                    <h3>Instructor Performance</h3>
                    <p>Interview assignments and completion rates by instructor</p>
                </div>
                <div class="instructor-performance">
                    @foreach($analytics['instructor_performance'] as $instructor)
                        <div class="instructor-item">
                            <div class="instructor-info">
                                <div class="instructor-name">{{ $instructor->full_name }}</div>
                                <div class="instructor-stats">
                                    Total: {{ $instructor->interviews_count }} | 
                                    Completed: {{ $instructor->completed_interviews_count }} |
                                    Rate: {{ $instructor->interviews_count > 0 ? round(($instructor->completed_interviews_count / $instructor->interviews_count) * 100, 1) : 0 }}%
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $instructor->interviews_count > 0 ? ($instructor->completed_interviews_count / $instructor->interviews_count) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Interview Outcomes -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Interview Recommendations</h3>
                    <p>Distribution of final recommendations</p>
                </div>
                <div class="outcomes-list">
                    @foreach($analytics['outcomes'] as $outcome => $count)
                        <div class="outcome-item">
                            <div class="outcome-label">{{ ucfirst(str_replace('_', ' ', $outcome)) }}</div>
                            <div class="outcome-count">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Trends -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Interview Trends (30 Days)</h3>
                    <p>Daily interview activity over the past month</p>
                </div>
                <div class="chart-container">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Insights -->
        <div class="insights-section">
            <h2 class="section-title">📊 Key Insights</h2>
            
            <div class="insights-grid">
                <div class="insight-card">
                    <div class="insight-header">
                        <h4>🏆 Top Performing Areas</h4>
                    </div>
                    <div class="insight-content">
                        @php
                            $topRating = collect($analytics['average_ratings'])->sortDesc()->first();
                            $topCategory = collect($analytics['average_ratings'])->sortDesc()->keys()->first();
                        @endphp
                        <p><strong>{{ ucfirst(str_replace('_', ' ', $topCategory)) }}</strong> scores highest with an average of <strong>{{ round($topRating, 1) }}</strong> points.</p>
                        <p class="insight-recommendation">🎯 Continue focusing on strengths in this area while developing other categories.</p>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-header">
                        <h4>📈 Improvement Opportunities</h4>
                    </div>
                    <div class="insight-content">
                        @php
                            $lowRating = collect($analytics['average_ratings'])->sort()->first();
                            $lowCategory = collect($analytics['average_ratings'])->sort()->keys()->first();
                        @endphp
                        <p><strong>{{ ucfirst(str_replace('_', ' ', $lowCategory)) }}</strong> shows room for improvement with an average of <strong>{{ round($lowRating, 1) }}</strong> points.</p>
                        <p class="insight-recommendation">💡 Consider additional training or modified evaluation criteria for this area.</p>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-header">
                        <h4>⚡ Efficiency Metrics</h4>
                    </div>
                    <div class="insight-content">
                        @php
                            $totalScheduled = $analytics['completion_rate']['scheduled'];
                            $totalCompleted = $analytics['completion_rate']['completed'];
                            $efficiencyRate = $totalScheduled > 0 ? round(($totalCompleted / $totalScheduled) * 100, 1) : 0;
                        @endphp
                        <p>Interview completion efficiency is at <strong>{{ $efficiencyRate }}%</strong> with {{ $totalCompleted }} out of {{ $totalScheduled }} scheduled interviews completed.</p>
                        <p class="insight-recommendation">📅 Consider optimizing scheduling processes to reduce no-shows and cancellations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Completion Rate Pie Chart
        const completionCtx = document.getElementById('completionChart').getContext('2d');
        new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Scheduled', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [
                        {{ $analytics['completion_rate']['scheduled'] }},
                        {{ $analytics['completion_rate']['completed'] }},
                        {{ $analytics['completion_rate']['cancelled'] }}
                    ],
                    backgroundColor: ['#3B82F6', '#10B981', '#EF4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Average Ratings Bar Chart
        const ratingsCtx = document.getElementById('ratingsChart').getContext('2d');
        new Chart(ratingsCtx, {
            type: 'bar',
            data: {
                labels: ['Technical', 'Communication', 'Problem Solving', 'Overall'],
                datasets: [{
                    data: [
                        {{ round($analytics['average_ratings']['technical'] ?? 0, 1) }},
                        {{ round($analytics['average_ratings']['communication'] ?? 0, 1) }},
                        {{ round($analytics['average_ratings']['problem_solving'] ?? 0, 1) }},
                        {{ round($analytics['average_ratings']['overall'] ?? 0, 1) }}
                    ],
                    backgroundColor: '#8B0000',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Trends Line Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($analytics['recent_trends'] as $trend)
                        '{{ Carbon\Carbon::parse($trend->date)->format('M d') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Interviews',
                    data: [
                        @foreach($analytics['recent_trends'] as $trend)
                            {{ $trend->count }},
                        @endforeach
                    ],
                    borderColor: '#8B0000',
                    backgroundColor: 'rgba(139, 0, 0, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function generateReport() {
            alert('Report generation feature - to be implemented with PDF export');
        }
    </script>

    <style>
        /* CSS Variables */
        :root {
            --white: #FFFFFF;
            --maroon-primary: #8B0000;
            --maroon-dark: #6B0000;
            --yellow-primary: #FFD700;
            --border-gray: #E5E7EB;
            --light-gray: #F9FAFB;
            --text-dark: #1F2937;
            --text-gray: #6B7280;
            --transition: all 0.3s ease;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .admin-container {
            min-height: 100vh;
            padding: 20px;
        }

        /* Header */
        .admin-header {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 16px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 24px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--maroon-primary), var(--maroon-dark));
            color: var(--white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--maroon-primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-gray);
            font-weight: 500;
        }

        /* Analytics Grid */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .chart-card.full-width {
            grid-column: 1 / -1;
        }

        .chart-header {
            margin-bottom: 25px;
        }

        .chart-header h3 {
            color: var(--maroon-primary);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .chart-header p {
            color: var(--text-gray);
            font-size: 14px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Instructor Performance */
        .instructor-performance {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .instructor-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            border-radius: 8px;
            background: var(--light-gray);
        }

        .instructor-info {
            flex: 1;
        }

        .instructor-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .instructor-stats {
            font-size: 14px;
            color: var(--text-gray);
        }

        .progress-bar {
            width: 200px;
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, var(--maroon-primary), var(--maroon-dark));
            transition: var(--transition);
        }

        /* Outcomes */
        .outcomes-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .outcome-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            background: var(--light-gray);
        }

        .outcome-label {
            font-weight: 500;
            color: var(--text-dark);
        }

        .outcome-count {
            font-weight: 700;
            color: var(--maroon-primary);
            background: var(--white);
            padding: 5px 12px;
            border-radius: 20px;
        }

        /* Insights Section */
        .insights-section {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 25px;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .insight-card {
            border: 2px solid var(--border-gray);
            border-radius: 12px;
            padding: 20px;
            transition: var(--transition);
        }

        .insight-card:hover {
            border-color: var(--maroon-primary);
            transform: translateY(-2px);
        }

        .insight-header h4 {
            color: var(--maroon-primary);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .insight-content p {
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .insight-recommendation {
            color: var(--text-gray);
            font-style: italic;
            font-size: 14px;
        }

        /* Buttons */
        .btn-primary, .btn-secondary {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--maroon-primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--maroon-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--text-gray);
            color: var(--white);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .instructor-item {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .progress-bar {
                width: 100%;
            }
        }

        @media (max-width: 1200px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>