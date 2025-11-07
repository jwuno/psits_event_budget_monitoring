<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'student') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get budget statistics
$sql = "SELECT 
    status,
    COUNT(*) as count,
    SUM(proposed_budget) as total_budget
    FROM proposals 
    GROUP BY status";
$result = mysqli_query($conn, $sql);
$budget_stats = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get monthly budget data
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
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="header-with-back">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h1>Budget Reports</h1>
                <p>Detailed budget allocation and utilization reports</p>
            </div>
        </div>
    </div>

    <!-- Budget Overview -->
    <div class="budget-overview">
        <h3>Budget Overview</h3>
        <div class="budget-stats">
            <?php foreach ($budget_stats as $stat): ?>
                <div class="budget-stat">
                    <h4><?php echo ucfirst($stat['status']); ?> Proposals</h4>
                    <div class="stat-numbers">
                        <span class="count"><?php echo $stat['count']; ?> proposals</span>
                        <span class="budget">₱<?php echo number_format($stat['total_budget'], 2); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="monthly-breakdown">
        <h3>Monthly Budget Allocation (Last 6 Months)</h3>
        <div class="monthly-table">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Proposals</th>
                        <th>Total Budget</th>
                        <th>Average per Proposal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($monthly_data)): ?>
                        <?php foreach ($monthly_data as $month): ?>
                            <tr>
                                <td><?php echo date('F Y', mktime(0, 0, 0, $month['month'], 1, $month['year'])); ?></td>
                                <td><?php echo $month['proposal_count']; ?></td>
                                <td>₱<?php echo number_format($month['monthly_budget'], 2); ?></td>
                                <td>₱<?php echo number_format($month['monthly_budget'] / $month['proposal_count'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">No budget data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Budget by Category -->
    <div class="category-breakdown">
        <h3>Budget Distribution</h3>
        <div class="category-stats">
            <div class="category-stat">
                <span class="category-name">Academic Events</span>
                <span class="category-budget">₱45,000.00</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 45%"></div>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-name">Technical Workshops</span>
                <span class="category-budget">₱35,000.00</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 35%"></div>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-name">Social Events</span>
                <span class="category-budget">₱20,000.00</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 20%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>