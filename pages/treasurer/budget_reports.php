<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Monthly budget data
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

// Top proposals by budget
$top_sql = "SELECT * FROM proposals WHERE status = 'approved' ORDER BY proposed_budget DESC LIMIT 5";
$top_result = mysqli_query($conn, $top_sql);
$top_proposals = mysqli_fetch_all($top_result, MYSQLI_ASSOC);

// Budget statistics
$stats_sql = "SELECT 
    SUM(proposed_budget) as total_approved,
    AVG(proposed_budget) as avg_budget,
    MAX(proposed_budget) as max_budget,
    MIN(proposed_budget) as min_budget
    FROM proposals WHERE status = 'approved'";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Financial Reports</h1>
            <p>Budget analytics and reports</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <div class="reports-grid">
        <div class="report-card">
            <h3>Budget Statistics</h3>
            <div class="stats-list">
                <div class="stat-item">
                    <span>Total Approved Budget:</span>
                    <strong>₱<?php echo number_format($stats['total_approved'], 2); ?></strong>
                </div>
                <div class="stat-item">
                    <span>Average Budget:</span>
                    <strong>₱<?php echo number_format($stats['avg_budget'], 2); ?></strong>
                </div>
                <div class="stat-item">
                    <span>Highest Budget:</span>
                    <strong>₱<?php echo number_format($stats['max_budget'], 2); ?></strong>
                </div>
                <div class="stat-item">
                    <span>Lowest Budget:</span>
                    <strong>₱<?php echo number_format($stats['min_budget'], 2); ?></strong>
                </div>
            </div>
        </div>

        <div class="report-card">
            <h3>Top Budget Proposals</h3>
            <div class="top-list">
                <?php if (!empty($top_proposals)): ?>
                    <?php foreach ($top_proposals as $proposal): ?>
                        <div class="top-item">
                            <span class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></span>
                            <span class="proposal-budget">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No approved proposals yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="report-card full-width">
            <h3>Monthly Budget Allocation</h3>
            <div class="monthly-table">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Proposals</th>
                            <th>Budget Allocated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($monthly_data)): ?>
                            <?php foreach ($monthly_data as $month): ?>
                                <tr>
                                    <td><?php echo date('F Y', mktime(0, 0, 0, $month['month'], 1, $month['year'])); ?></td>
                                    <td><?php echo $month['proposal_count']; ?></td>
                                    <td>₱<?php echo number_format($month['monthly_budget'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No monthly data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>