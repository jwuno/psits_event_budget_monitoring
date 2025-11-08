<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get proposals waiting for treasurer review
$pending_treasurer_sql = "SELECT * FROM proposals 
                         WHERE status = 'pending'
                         ORDER BY date_submitted DESC 
                         LIMIT 5";
$pending_treasurer_result = mysqli_query($conn, $pending_treasurer_sql);
$pending_treasurer = mysqli_fetch_all($pending_treasurer_result, MYSQLI_ASSOC);

// Budget statistics for pending proposals
$budget_stats_sql = "SELECT 
    COUNT(*) as total_proposals,
    SUM(proposed_budget) as total_budget_requested,
    AVG(proposed_budget) as avg_budget
    FROM proposals 
    WHERE status = 'pending'";
$budget_stats_result = mysqli_query($conn, $budget_stats_sql);
$budget_stats = mysqli_fetch_assoc($budget_stats_result);

// Get financial overview
$financial_sql = "SELECT 
    SUM(CASE WHEN status = 'approved' THEN proposed_budget ELSE 0 END) as total_approved_budget,
    SUM(CASE WHEN status = 'pending' THEN proposed_budget ELSE 0 END) as total_pending_budget,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
    FROM proposals";
$financial_result = mysqli_query($conn, $financial_sql);
$financial = mysqli_fetch_assoc($financial_result);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Treasurer Dashboard</h1>
            <p>Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>! Budget review and financial oversight.</p>
        </div>
    </div>

    <!-- Financial Overview Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo $financial['pending_count']; ?></h3>
                <p>Pending Review</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($financial['total_pending_budget'], 2); ?></h3>
                <p>Pending Budget</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $financial['approved_count']; ?></h3>
                <p>Approved Proposals</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💸</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($financial['total_approved_budget'], 2); ?></h3>
                <p>Approved Budget</p>
            </div>
        </div>
    </div>

    <!-- Pending Budget Reviews -->
    <div class="pending-reviews">
        <div class="reviews-header">
            <h3>Proposals Awaiting Budget Review</h3>
            <?php if (!empty($pending_treasurer)): ?>
                <a href="review_proposals.php" class="view-all">Review All</a>
            <?php endif; ?>
        </div>
        <div class="reviews-list">
            <?php if (!empty($pending_treasurer)): ?>
                <?php foreach ($pending_treasurer as $proposal): ?>
                    <div class="review-item">
                        <div class="review-icon">📋</div>
                        <div class="review-content">
                            <p class="review-title"><?php echo htmlspecialchars($proposal['title']); ?></p>
                            <div class="proposal-details">
                                <span class="budget">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                                <span class="participants"><?php echo $proposal['expected_participants']; ?> participants</span>
                                <span class="submitter">By: <?php echo htmlspecialchars($proposal['created_by']); ?></span>
                            </div>
                            <?php if (!empty($proposal['budget_breakdown'])): ?>
                                <div class="budget-preview">
                                    <strong>Budget Breakdown:</strong> 
                                    <?php echo substr(strip_tags($proposal['budget_breakdown']), 0, 100); ?>...
                                </div>
                            <?php endif; ?>
                            <span class="review-time">Submitted: <?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></span>
                        </div>
                        <div class="review-actions">
                            <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn-review">
                                <i class="fas fa-search-dollar"></i> Review Budget
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">✅</div>
                    <h4>All Caught Up!</h4>
                    <p>No proposals awaiting budget review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Budget Summary -->
    <div class="budget-summary">
        <h3>Budget Overview</h3>
        <div class="summary-cards">
            <div class="summary-item">
                <span class="label">Total Budget Pool</span>
                <span class="value">₱150,000.00</span>
            </div>
            <div class="summary-item">
                <span class="label">Allocated Budget</span>
                <span class="value">₱<?php echo number_format($financial['total_approved_budget'], 2); ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Available Budget</span>
                <span class="value">₱<?php echo number_format(150000 - $financial['total_approved_budget'], 2); ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Utilization Rate</span>
                <span class="value"><?php echo number_format(($financial['total_approved_budget'] / 150000) * 100, 1); ?>%</span>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>