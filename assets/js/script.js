// assets/js/script.js - Simple Working Version
document.addEventListener('DOMContentLoaded', function() {
    console.log("✅ JavaScript loaded!");

    const profileIcon = document.getElementById('profileIcon');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');

    // Initialize - hide dropdowns
    if (dropdownMenu) dropdownMenu.style.display = 'none';
    if (notificationDropdown) notificationDropdown.style.display = 'none';

    // Profile dropdown
    if (profileIcon && dropdownMenu) {
        profileIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log("🎯 Profile clicked");
            
            if (dropdownMenu.style.display === 'block') {
                dropdownMenu.style.display = 'none';
            } else {
                dropdownMenu.style.display = 'block';
                // Hide notifications if open
                if (notificationDropdown) notificationDropdown.style.display = 'none';
            }
        });
    }

    // Notifications dropdown
    if (notificationIcon && notificationDropdown) {
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log("🔔 Notifications clicked");
            
            if (notificationDropdown.style.display === 'block') {
                notificationDropdown.style.display = 'none';
            } else {
                notificationDropdown.style.display = 'block';
                // Hide profile if open
                if (dropdownMenu) dropdownMenu.style.display = 'none';
            }
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (dropdownMenu && dropdownMenu.style.display === 'block') {
            if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.style.display = 'none';
            }
        }
        if (notificationDropdown && notificationDropdown.style.display === 'block') {
            if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.style.display = 'none';
            }
        }
    });

    console.log("🎉 All functions ready!");
});

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../../logout.php';
    }
}

// Mark all as read
function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
        const badge = document.querySelector('.notification-badge');
        const markReadBtn = document.querySelector('.mark-read-btn');
        if (badge) badge.remove();
        if (markReadBtn) markReadBtn.style.display = 'none';
        alert('Notifications marked as read!');
    }
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Proposals Status Chart (Doughnut)
    const proposalsCtx = document.getElementById('proposalsChart').getContext('2d');
    const proposalsChart = new Chart(proposalsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected', 'Draft'],
            datasets: [{
                data: [3, 1, 1, 2],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
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

    // Budget Allocation Chart (Bar)
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: ['Tech', 'Sports', 'Academic', 'Social', 'Other'],
            datasets: [{
                label: 'Budget (₱)',
                data: [5000, 3000, 4000, 2000, 1000],
                backgroundColor: [
                    '#007bff',
                    '#28a745', 
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ],
                borderWidth: 1
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
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Monthly Activity Chart (Line)
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Proposals Submitted',
                data: [2, 3, 1, 4, 2, 3],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Proposals Approved',
                data: [1, 2, 1, 3, 1, 2],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
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
                        stepSize: 1
                    }
                }
            }
        }
    });
});