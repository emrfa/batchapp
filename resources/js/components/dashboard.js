export default () => ({
    stats: {
        todaysCount: 0,
        avgCycleTime: 0,
        totalVolumeTons: 0,
        mixers: [],
        silos: [],
        chart: { labels: [], data: [] }
    },
    range: 'today',
    isLoading: true,

    init() {
        this.initChart();
        this.fetchData();
        // Poll every 30 seconds
        setInterval(() => this.fetchData(), 30000);
    },

    initChart() {
        const ctx = document.getElementById('materialChart');
        if (ctx) {
            window.myMaterialChart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Consumed (kg)',
                        data: [],
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(20, 184, 166, 0.8)',
                            'rgba(245, 158, 11, 0.8)'
                        ],
                        borderColor: [
                            'rgb(99, 102, 241)',
                            'rgb(139, 92, 246)',
                            'rgb(236, 72, 153)',
                            'rgb(20, 184, 166)',
                            'rgb(245, 158, 11)'
                        ],
                        borderWidth: 0,
                        borderRadius: 6,
                        barPercentage: 0.5,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: (context) => {
                                    const unit = (this.stats.chart && this.stats.chart.units) ? this.stats.chart.units[context.dataIndex] : 'kg';
                                    return context.parsed.x.toLocaleString() + ' ' + unit;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#f3f4f6',
                                drawBorder: false,
                            },
                            ticks: {
                                font: { family: "'Inter', sans-serif", size: 11 },
                                color: '#9ca3af'
                            }
                        },
                        y: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                font: { family: "'Inter', sans-serif", weight: '600', size: 12 },
                                color: '#374151'
                            }
                        }
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    },

    async fetchData() {
        try {
            const response = await fetch(`/api/dashboard/stats?filter=${this.range}`);
            const data = await response.json();
            this.stats = data;
            this.isLoading = false;
            this.updateChart();
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
        }
    },

    updateChart() {
        if (window.myMaterialChart && this.stats.chart) {
            window.myMaterialChart.data.labels = this.stats.chart.labels;
            window.myMaterialChart.data.datasets[0].data = this.stats.chart.data;
            window.myMaterialChart.update();
        }
    }
});
