jQuery(document).ready(function($) {
    if (!document.getElementById('accuracyChart')) {
        return;
    }

    const nonce = $('#attentrack_chart_nonce').val();
    
    // Fetch chart data
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'attentrack_get_chart_data',
            nonce: nonce
        },
        success: function(response) {
            if (response.success && response.data) {
                renderCharts(response.data);
            } else {
                $('#chartError').show();
            }
        },
        error: function() {
            $('#chartError').show();
        }
    });

    function renderCharts(data) {
        const dates = data.map(item => item.date);
        const accuracies = data.map(item => parseFloat(item.avg_accuracy));
        const reactionTimes = data.map(item => parseFloat(item.avg_reaction_time));
        const testCounts = data.map(item => parseInt(item.test_count));

        // Accuracy Chart
        new Chart(document.getElementById('accuracyChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Average Accuracy (%)',
                    data: accuracies,
                    borderColor: '#2271b1',
                    backgroundColor: 'rgba(34, 113, 177, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Average Accuracy Trend'
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Accuracy (%)'
                        }
                    }
                }
            }
        });

        // Reaction Time Chart
        new Chart(document.getElementById('reactionTimeChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Average Reaction Time (s)',
                    data: reactionTimes,
                    borderColor: '#e65100',
                    backgroundColor: 'rgba(230, 81, 0, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Average Reaction Time Trend'
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        title: {
                            display: true,
                            text: 'Reaction Time (seconds)'
                        }
                    }
                }
            }
        });

        // Test Count Chart
        new Chart(document.getElementById('testCountChart'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Tests Taken',
                    data: testCounts,
                    backgroundColor: 'rgba(46, 125, 50, 0.7)',
                    borderColor: '#2e7d32',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Test Count'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Tests'
                        }
                    }
                }
            }
        });
    }
});
