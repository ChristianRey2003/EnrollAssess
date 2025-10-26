/**
 * Charts Manager for Analytics Dashboard
 * Handles creating and updating Chart.js visualizations
 */

import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

class ChartsManager {
    constructor() {
        this.charts = {};
        this.defaultColors = {
            maroon: 'rgb(128, 0, 32)',
            yellow: 'rgb(255, 215, 0)',
            blue: 'rgb(59, 130, 246)',
            green: 'rgb(34, 197, 94)',
            red: 'rgb(239, 68, 68)',
            gray: 'rgb(107, 114, 128)',
        };

        this.init();
    }

    init() {
        console.log('Initializing Charts Manager...');
        this.loadAllCharts();
    }

    /**
     * Load all charts on the page
     */
    loadAllCharts() {
        document.querySelectorAll('[data-chart-type]').forEach(canvas => {
            const chartType = canvas.dataset.chartType;
            const chartId = canvas.id;
            const endpoint = canvas.dataset.endpoint;

            if (chartType && chartId && endpoint) {
                this.loadAndRenderChart(chartId, chartType, endpoint, canvas);
            }
        });
    }

    /**
     * Load chart data and render
     */
    async loadAndRenderChart(chartId, chartType, endpoint, canvas) {
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch chart data from ${endpoint}`);
            }

            const result = await response.json();
            
            if (result.success && result.data) {
                this.renderChart(chartId, chartType, result.data, canvas);
            } else {
                console.error('Invalid chart data:', result);
                this.showChartError(canvas, 'No data available');
            }
        } catch (error) {
            console.error(`Error loading chart ${chartId}:`, error);
            this.showChartError(canvas, 'Failed to load chart');
        }
    }

    /**
     * Render a chart
     */
    renderChart(chartId, chartType, data, canvas) {
        // Destroy existing chart
        if (this.charts[chartId]) {
            this.charts[chartId].destroy();
        }

        // Get chart configuration based on type
        const config = this.getChartConfig(chartType, data);

        // Create chart
        try {
            this.charts[chartId] = new Chart(canvas, config);
            
            // Remove loading state
            const container = canvas.closest('.chart-container');
            if (container) {
                container.classList.remove('chart-loading');
                container.classList.add('chart-loaded');
            }
        } catch (error) {
            console.error(`Error creating chart ${chartId}:`, error);
            this.showChartError(canvas, 'Failed to render chart');
        }
    }

    /**
     * Get chart configuration
     */
    getChartConfig(chartType, data) {
        const configs = {
            'line': this.getLineChartConfig(data),
            'bar': this.getBarChartConfig(data),
            'pie': this.getPieChartConfig(data),
            'doughnut': this.getDoughnutChartConfig(data),
            'radar': this.getRadarChartConfig(data),
        };

        return configs[chartType] || this.getLineChartConfig(data);
    }

    /**
     * Line chart configuration
     */
    getLineChartConfig(data) {
        return {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                        },
                        bodyFont: {
                            size: 13,
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y !== null ? 
                                    Math.round(context.parsed.y * 100) / 100 : '';
                                return label;
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                        },
                        ticks: {
                            font: {
                                size: 11,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 11,
                            },
                        },
                    },
                },
            },
        };
    }

    /**
     * Bar chart configuration
     */
    getBarChartConfig(data) {
        return {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        };
    }

    /**
     * Pie chart configuration
     */
    getPieChartConfig(data) {
        return {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        
                                        return {
                                            text: `${label} (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i,
                                        };
                                    });
                                }
                                return [];
                            },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            },
                        },
                    },
                },
            },
        };
    }

    /**
     * Doughnut chart configuration
     */
    getDoughnutChartConfig(data) {
        const config = this.getPieChartConfig(data);
        config.type = 'doughnut';
        return config;
    }

    /**
     * Radar chart configuration
     */
    getRadarChartConfig(data) {
        return {
            type: 'radar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 20,
                        },
                    },
                },
            },
        };
    }

    /**
     * Show error message on chart
     */
    showChartError(canvas, message) {
        const container = canvas.closest('.chart-container');
        if (container) {
            container.classList.remove('chart-loading');
            container.classList.add('chart-error');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'chart-error-message';
            errorDiv.textContent = message;
            container.appendChild(errorDiv);
        }
    }

    /**
     * Refresh a specific chart
     */
    async refreshChart(chartId) {
        const canvas = document.getElementById(chartId);
        if (!canvas) {
            console.error(`Chart canvas not found: ${chartId}`);
            return;
        }

        const chartType = canvas.dataset.chartType;
        const endpoint = canvas.dataset.endpoint;

        if (!chartType || !endpoint) {
            console.error(`Chart metadata missing for: ${chartId}`);
            return;
        }

        // Show loading state
        const container = canvas.closest('.chart-container');
        if (container) {
            container.classList.add('chart-loading');
        }

        await this.loadAndRenderChart(chartId, chartType, endpoint, canvas);
    }

    /**
     * Refresh all charts
     */
    async refreshAllCharts() {
        const promises = Object.keys(this.charts).map(chartId => 
            this.refreshChart(chartId)
        );
        
        await Promise.all(promises);
    }

    /**
     * Export chart as image
     */
    exportChartAsImage(chartId, filename = 'chart.png') {
        const chart = this.charts[chartId];
        if (!chart) {
            console.error(`Chart not found: ${chartId}`);
            return;
        }

        const url = chart.toBase64Image();
        const link = document.createElement('a');
        link.download = filename;
        link.href = url;
        link.click();
    }

    /**
     * Destroy all charts
     */
    destroyAllCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart) {
                chart.destroy();
            }
        });
        this.charts = {};
    }
}

// Initialize charts manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.chartsManager = new ChartsManager();
    });
} else {
    window.chartsManager = new ChartsManager();
}

export default ChartsManager;

