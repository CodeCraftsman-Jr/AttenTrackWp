jQuery(document).ready(function($) {
    // Chart instances
    let performanceChart = null;
    let responseChart = null;
    let errorChart = null;

    // Initialize date inputs
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);

    $('#date-from').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#date-to').val(today.toISOString().split('T')[0]);

    // Chart configuration defaults
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        },
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'day',
                    displayFormats: {
                        day: 'MMM d'
                    }
                },
                title: {
                    display: true,
                    text: 'Date'
                }
            }
        }
    };

    // Initialize charts
    function initializeCharts() {
        // Performance Chart
        const performanceCtx = document.getElementById('performance-chart').getContext('2d');
        performanceChart = new Chart(performanceCtx, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: 'Accuracy',
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    },
                    {
                        label: 'Completion Rate',
                        borderColor: 'rgb(153, 102, 255)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                ...chartDefaults,
                scales: {
                    ...chartDefaults.scales,
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    }
                }
            }
        });

        // Response Time Chart
        const responseCtx = document.getElementById('response-chart').getContext('2d');
        responseChart = new Chart(responseCtx, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: 'Average',
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1
                    },
                    {
                        label: 'Minimum',
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    },
                    {
                        label: 'Maximum',
                        borderColor: 'rgb(255, 159, 64)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                ...chartDefaults,
                scales: {
                    ...chartDefaults.scales,
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Response Time (ms)'
                        }
                    }
                }
            }
        });

        // Error Chart
        const errorCtx = document.getElementById('error-chart').getContext('2d');
        errorChart = new Chart(errorCtx, {
            type: 'bar',
            data: {
                datasets: [
                    {
                        label: 'Commission Errors',
                        backgroundColor: 'rgb(255, 99, 132)',
                    },
                    {
                        label: 'Omission Errors',
                        backgroundColor: 'rgb(255, 159, 64)',
                    }
                ]
            },
            options: {
                ...chartDefaults,
                scales: {
                    ...chartDefaults.scales,
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Errors'
                        }
                    }
                }
            }
        });
    }

    // Update charts with new data
    function updateCharts(data) {
        // Performance Chart
        performanceChart.data.labels = data.performance.dates;
        performanceChart.data.datasets[0].data = data.performance.accuracy;
        performanceChart.data.datasets[1].data = data.performance.completion_rate;
        performanceChart.update();

        // Response Time Chart
        responseChart.data.labels = data.response_times.dates;
        responseChart.data.datasets[0].data = data.response_times.average;
        responseChart.data.datasets[1].data = data.response_times.minimum;
        responseChart.data.datasets[2].data = data.response_times.maximum;
        responseChart.update();

        // Error Chart
        errorChart.data.labels = data.errors.dates;
        errorChart.data.datasets[0].data = data.errors.commission;
        errorChart.data.datasets[1].data = data.errors.omission;
        errorChart.update();

        // Update summary table
        updateSummaryTable(data.summary);
    }

    // Update summary table
    function updateSummaryTable(summaryData) {
        const table = $('<table class="wp-list-table widefat fixed striped">').append(
            $('<thead>').append(
                $('<tr>').append(
                    $('<th>').text('Date'),
                    $('<th>').text('Test Type'),
                    $('<th>').text('Duration (s)'),
                    $('<th>').text('Accuracy (%)'),
                    $('<th>').text('Completion (%)'),
                    $('<th>').text('Avg Response (ms)'),
                    $('<th>').text('Total Errors')
                )
            )
        );

        const tbody = $('<tbody>');
        summaryData.forEach(row => {
            tbody.append(
                $('<tr>').append(
                    $('<td>').text(row.date),
                    $('<td>').text(row.test_type),
                    $('<td>').text(row.duration),
                    $('<td>').text(row.accuracy.toFixed(2)),
                    $('<td>').text(row.completion_rate.toFixed(2)),
                    $('<td>').text(row.avg_response_time.toFixed(2)),
                    $('<td>').text(row.total_errors)
                )
            );
        });

        table.append(tbody);
        $('#summary-table').empty().append(table);
    }

    // Load report data
    function loadReportData() {
        const userId = $('#user-select').val();
        const fromDate = $('#date-from').val();
        const toDate = $('#date-to').val();
        const testType = $('#test-type').val();

        $('.report-container').addClass('loading');

        $.ajax({
            url: attentrackReports.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_report_data',
                nonce: attentrackReports.nonce,
                user_id: userId,
                from_date: fromDate,
                to_date: toDate,
                test_type: testType
            },
            success: function(response) {
                if (response.success) {
                    updateCharts(response.data);
                } else {
                    alert(response.data || attentrackReports.i18n.error);
                }
            },
            error: function() {
                alert(attentrackReports.i18n.error);
            },
            complete: function() {
                $('.report-container').removeClass('loading');
            }
        });
    }

    // Export report data
    function exportReportData() {
        const userId = $('#user-select').val();
        const fromDate = $('#date-from').val();
        const toDate = $('#date-to').val();
        const testType = $('#test-type').val();

        $.ajax({
            url: attentrackReports.ajaxUrl,
            type: 'POST',
            data: {
                action: 'export_report',
                nonce: attentrackReports.nonce,
                user_id: userId,
                from_date: fromDate,
                to_date: toDate,
                test_type: testType
            },
            success: function(response) {
                if (response.success) {
                    // Create and trigger download
                    const blob = new Blob([response.data.csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = response.data.filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    alert(response.data || attentrackReports.i18n.error);
                }
            },
            error: function() {
                alert(attentrackReports.i18n.error);
            }
        });
    }

    // Event handlers
    $('#update-report').on('click', loadReportData);
    $('#export-report').on('click', exportReportData);
    $('#user-select').on('change', loadReportData);

    // Initialize on page load
    initializeCharts();
    if ($('#user-select').length) {
        loadReportData();
    }
});
