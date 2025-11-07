<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'student') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get real data from database
// Total proposals count
$total_sql = "SELECT COUNT(*) as total FROM proposals";
$total_result = mysqli_query($conn, $total_sql);
$total_proposals = mysqli_fetch_assoc($total_result)['total'];

// Approved proposals count
$approved_sql = "SELECT COUNT(*) as approved FROM proposals WHERE status = 'approved'";
$approved_result = mysqli_query($conn, $approved_sql);
$approved_proposals = mysqli_fetch_assoc($approved_result)['approved'];

// Pending proposals count
$pending_sql = "SELECT COUNT(*) as pending FROM proposals WHERE status = 'pending'";
$pending_result = mysqli_query($conn, $pending_sql);
$pending_proposals = mysqli_fetch_assoc($pending_result)['pending'];

// Rejected proposals count
$rejected_sql = "SELECT COUNT(*) as rejected FROM proposals WHERE status = 'rejected'";
$rejected_result = mysqli_query($conn, $rejected_sql);
$rejected_proposals = mysqli_fetch_assoc($rejected_result)['rejected'];

// Total budget requested
$total_budget_sql = "SELECT SUM(proposed_budget) as total_budget FROM proposals";
$total_budget_result = mysqli_query($conn, $total_budget_sql);
$total_budget_row = mysqli_fetch_assoc($total_budget_result);
$total_budget = $total_budget_row['total_budget'] ?: 0;

// Utilized budget (approved proposals)
$utilized_sql = "SELECT SUM(proposed_budget) as utilized_budget FROM proposals WHERE status = 'approved'";
$utilized_result = mysqli_query($conn, $utilized_sql);
$utilized_row = mysqli_fetch_assoc($utilized_result);
$utilized_budget = $utilized_row['utilized_budget'] ?: 0;

// Get data for charts
// Proposals by status for doughnut chart
$status_sql = "SELECT status, COUNT(*) as count FROM proposals GROUP BY status";
$status_result = mysqli_query($conn, $status_sql);
$status_data = [];
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_data[$row['status']] = $row['count'];
}

// Monthly data for line chart
$monthly_sql = "SELECT 
    MONTH(date_submitted) as month,
    YEAR(date_submitted) as year,
    COUNT(*) as proposal_count,
    SUM(proposed_budget) as monthly_budget
    FROM proposals 
    WHERE status = 'approved'
    GROUP BY YEAR(date_submitted), MONTH(date_submitted)
    ORDER BY year, month
    LIMIT 6";
$monthly_result = mysqli_query($conn, $monthly_sql);
$monthly_data = mysqli_fetch_all($monthly_result, MYSQLI_ASSOC);

// Get recent activities
$recent_sql = "SELECT * FROM proposals 
               WHERE status = 'approved' 
               ORDER BY updated_at DESC 
               LIMIT 5";
$recent_result = mysqli_query($conn, $recent_sql);
$recent_activities = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Student Dashboard</h1>
            <p>Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>! View real-time budget transparency and proposal status.</p>
        </div>
        <div class="header-actions">
            <div class="last-updated">
                <i class="fas fa-sync-alt"></i>
                Last updated: <?php echo date('M j, Y g:i A'); ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo $total_proposals; ?></h3>
                <p>Total Proposals</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $approved_proposals; ?></h3>
                <p>Approved</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($total_budget, 2); ?></h3>
                <p>Total Budget</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💸</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($utilized_budget, 2); ?></h3>
                <p>Utilized Budget</p>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Proposals Status</h3>
                <span class="chart-subtitle">Overall distribution</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="proposalsChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-money-bill-wave"></i> Budget Utilization</h3>
                <span class="chart-subtitle">Used vs Remaining</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="budgetChart"></canvas>
            </div>
        </div>

        <div class="chart-card full-width">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Monthly Budget Tracking</h3>
                <span class="chart-subtitle">Budget allocation over time</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Access & Recent Updates -->
    <div class="dashboard-grid">
        <!-- Quick Access -->
        <div class="quick-access">
            <h3>Quick Access</h3>
            <div class="access-buttons">
                <a href="all_proposals.php" class="access-btn">
                    <i class="fas fa-list-ol"></i>
                    <span>View All Proposals</span>
                </a>
                <a href="approved_proposals.php" class="access-btn">
                    <i class="fas fa-check-circle"></i>
                    <span>Approved Proposals</span>
                </a>
                <a href="budget_reports.php" class="access-btn">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Budget Reports</span>
                </a>
                <a href="financial_summary.php" class="access-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Financial Summary</span>
                </a>
            </div>
        </div>

        <!-- Recent Updates -->
        <div class="recent-updates">
            <div class="updates-header">
                <h3>Recent Updates</h3>
                <a href="updates_log.php" class="view-all">View All</a>
            </div>
            <div class="updates-list">
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="update-item approved">
                            <div class="update-icon">✅</div>
                            <div class="update-content">
                                <p class="update-title">New Proposal Approved</p>
                                <p class="update-desc">"<?php echo htmlspecialchars($activity['title']); ?>" - ₱<?php echo number_format($activity['proposed_budget'], 2); ?> budget allocated</p>
                                <span class="update-time"><?php echo date('M j, Y g:i A', strtotime($activity['updated_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="update-item">
                        <div class="update-icon">ℹ️</div>
                        <div class="update-content">
                            <p class="update-title">No Recent Activity</p>
                            <p class="update-desc">There are no recent updates to display.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transparency Notice -->
    <div class="transparency-notice">
        <div class="notice-icon">🔍</div>
        <div class="notice-content">
            <h4>Transparency Portal</h4>
            <p>This dashboard provides real-time access to PSITS budget allocations, proposal status, and financial reports for complete transparency.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Student Dashboard Charts with Real Data
document.addEventListener('DOMContentLoaded', function() {
    // Proposals Status Chart (Doughnut)
    const proposalsCtx = document.getElementById('proposalsChart').getContext('2d');
    const proposalsChart = new Chart(proposalsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    <?php echo $approved_proposals; ?>,
                    <?php echo $pending_proposals; ?>,
                    <?php echo $rejected_proposals; ?>
                ],
                backgroundColor: [
                    '#28a745', // Green for approved
                    '#ffc107', // Yellow for pending
                    '#dc3545'  // Red for rejected
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
                data: [
                    <?php echo $utilized_budget; ?>,
                    <?php echo max(0, $total_budget - $utilized_budget); ?>
                ],
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
            labels: [
                <?php 
                if (!empty($monthly_data)) {
                    $labels = [];
                    foreach ($monthly_data as $month) {
                        $labels[] = "'" . date('M Y', mktime(0, 0, 0, $month['month'], 1, $month['year'])) . "'";
                    }
                    echo implode(', ', $labels);
                } else {
                    echo "'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'";
                }
                ?>
            ],
            datasets: [{
                label: 'Budget Allocated (₱)',
                data: [
                    <?php 
                    if (!empty($monthly_data)) {
                        $budgets = [];
                        foreach ($monthly_data as $month) {
                            $budgets[] = $month['monthly_budget'];
                        }
                        echo implode(', ', $budgets);
                    } else {
                        echo "0, 0, 0, 0, 0, 0";
                    }
                    ?>
                ],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3
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
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include('../../includes/footer.php'); ?>