// Student Dashboard Charts - View Only
document.addEventListener('DOMContentLoaded', function() {
    // Proposals Status Chart (Doughnut)
    const proposalsCtx = document.getElementById('proposalsChart').getContext('2d');
    const proposalsChart = new Chart(proposalsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected', 'Completed'],
            datasets: [{
                data: [18, 4, 2, 8],
                backgroundColor: [
                    '#28a745', // Green for approved
                    '#ffc107', // Yellow for pending
                    '#dc3545', // Red for rejected
                    '#17a2b8'  // Blue for completed
                ],
                borderWidth: 2,
                borderColor: '#fff'
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw} proposals`;
                        }
                    }
                }
            }
        }
    });

    // Budget Utilization Chart (Pie)
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'pie',
        data: {
            labels: ['Utilized Budget', 'Remaining Budget'],
            datasets: [{
                data: [98000, 52000],
                backgroundColor: [
                    '#007bff',
                    '#e9ecef'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ₱${context.raw.toLocaleString()}`;
                        }
                    }
                }
            }
        }
    });

    // Monthly Budget Tracking (Line)
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Budget Allocated (₱)',
                data: [20000, 25000, 30000, 22000, 28000, 32000, 15000],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3
            }, {
                label: 'Proposals Submitted',
                data: [8, 12, 15, 10, 14, 18, 7],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: false,
                borderWidth: 2,
                borderDash: [5, 5]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (this.id === 'y') {
                                return '₱' + value.toLocaleString();
                            }
                            return value;
                        }
                    }
                }
            }
        }
    });
});