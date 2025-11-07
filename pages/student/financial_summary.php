<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'student') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get overall financial summary
$summary_sql = "SELECT 
    COUNT(*) as total_proposals,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_proposals,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_proposals,
    SUM(proposed_budget) as total_requested,
    SUM(CASE WHEN status = 'approved' THEN proposed_budget ELSE 0 END) as total_approved
    FROM proposals";
$summary_result = mysqli_query($conn, $summary_sql);
$summary = mysqli_fetch_assoc($summary_result);

// Get recent financial activities
$recent_sql = "SELECT * FROM proposals 
               WHERE status = 'approved' 
               ORDER BY updated_at DESC 
               LIMIT 10";
$recent_result = mysqli_query($conn, $recent_sql);
$recent_activities = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="header-with-back">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h1>Financial Summary</h1>
                <p>Comprehensive overview of PSITS financial activities</p>
            </div>
        </div>
    </div>

    <!-- Key Financial Metrics -->
    <div class="financial-metrics">
        <div class="metric-card">
            <div class="metric-icon">📊</div>
            <div class="metric-info">
                <h3><?php echo $summary['total_proposals']; ?></h3>
                <p>Total Proposals</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">✅</div>
            <div class="metric-info">
                <h3><?php echo $summary['approved_proposals']; ?></h3>
                <p>Approved Proposals</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">💰</div>
            <div class="metric-info">
                <h3>₱<?php echo number_format($summary['total_requested'], 2); ?></h3>
                <p>Total Requested</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">💸</div>
            <div class="metric-info">
                <h3>₱<?php echo number_format($summary['total_approved'], 2); ?></h3>
                <p>Total Approved</p>
            </div>
        </div>
    </div>

    <!-- Approval Rate -->
    <div class="approval-rate">
        <h3>Approval Rate</h3>
        <div class="rate-display">
            <?php 
            $approval_rate = $summary['total_proposals'] > 0 ? 
                ($summary['approved_proposals'] / $summary['total_proposals']) * 100 : 0;
            ?>
            <div class="rate-circle">
                <span class="rate-value"><?php echo number_format($approval_rate, 1); ?>%</span>
            </div>
            <div class="rate-details">
                <p><strong><?php echo $summary['approved_proposals']; ?></strong> out of <strong><?php echo $summary['total_proposals']; ?></strong> proposals approved</p>
                <p>Average approval rate across all submissions</p>
            </div>
        </div>
    </div>

    <!-- Recent Financial Activities -->
    <div class="recent-activities">
        <h3>Recent Financial Activities</h3>
        <div class="activities-list">
            <?php if (!empty($recent_activities)): ?>
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item financial">
                        <div class="activity-icon">💰</div>
                        <div class="activity-content">
                            <p class="activity-title">Budget Approved</p>
                            <p class="activity-desc">
                                "<?php echo htmlspecialchars($activity['title']); ?>" - 
                                ₱<?php echo number_format($activity['proposed_budget'], 2); ?> allocated
                            </p>
                            <span class="activity-time">
                                <?php echo date('M j, Y g:i A', strtotime($activity['updated_at'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No recent financial activities found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Budget Utilization -->
    <div class="budget-utilization">
        <h3>Budget Utilization</h3>
        <div class="utilization-stats">
            <div class="utilization-item">
                <span class="label">Total Budget Pool</span>
                <span class="value">₱150,000.00</span>
            </div>
            <div class="utilization-item">
                <span class="label">Utilized Budget</span>
                <span class="value">₱<?php echo number_format($summary['total_approved'], 2); ?></span>
            </div>
            <div class="utilization-item">
                <span class="label">Remaining Budget</span>
                <span class="value">₱<?php echo number_format(150000 - $summary['total_approved'], 2); ?></span>
            </div>
            <div class="utilization-item">
                <span class="label">Utilization Rate</span>
                <span class="value"><?php echo number_format(($summary['total_approved'] / 150000) * 100, 1); ?>%</span>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>