<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get counts for dashboard
$counts_sql = "SELECT 
    COUNT(*) as total_proposals,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
    SUM(CASE WHEN status = 'pending' THEN proposed_budget ELSE 0 END) as pending_budget,
    SUM(CASE WHEN status = 'approved' THEN proposed_budget ELSE 0 END) as approved_budget
    FROM proposals";
$counts_result = mysqli_query($conn, $counts_sql);
$counts = mysqli_fetch_assoc($counts_result);

// Get recent pending proposals
$recent_sql = "SELECT * FROM proposals WHERE status = 'pending' ORDER BY date_submitted DESC LIMIT 3";
$recent_result = mysqli_query($conn, $recent_sql);
$recent_proposals = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Treasurer Dashboard</h1>
            <p>Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>! Budget management and financial oversight.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo $counts['pending']; ?></h3>
                <p>Pending Approval</p>
                <a href="pending_reviews.php" class="stat-link">Review Pending</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo $counts['total_proposals']; ?></h3>
                <p>Total Proposals</p>
                <a href="all_proposals.php" class="stat-link">View All</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $counts['approved']; ?></h3>
                <p>Approved Proposals</p>
                <a href="approved_proposals.php" class="stat-link">View Approved</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-info">
                <h3><?php echo $counts['rejected']; ?></h3>
                <p>Rejected Proposals</p>
                <a href="rejected_proposals.php" class="stat-link">View Rejected</a>
            </div>
        </div>
    </div>

    <!-- Budget Overview -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($counts['pending_budget'], 2); ?></h3>
                <p>Pending Budget</p>
                <span class="stat-desc">Awaiting approval</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💸</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($counts['approved_budget'], 2); ?></h3>
                <p>Approved Budget</p>
                <span class="stat-desc">Total allocated</span>
            </div>
        </div>
    </div>

    <!-- Recent Pending Proposals -->
    <div class="recent-section">
        <div class="section-header">
            <h3>Recent Pending Proposals</h3>
            <a href="pending_reviews.php" class="view-all">View All</a>
        </div>
        
        <div class="recent-list">
            <?php if (!empty($recent_proposals)): ?>
                <?php foreach ($recent_proposals as $proposal): ?>
                    <div class="recent-item">
                        <div class="recent-icon">📋</div>
                        <div class="recent-content">
                            <h4><?php echo htmlspecialchars($proposal['title']); ?></h4>
                            <div class="recent-details">
                                <span>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                                <span><?php echo $proposal['expected_participants']; ?> participants</span>
                                <span>By: <?php echo htmlspecialchars($proposal['created_by']); ?></span>
                            </div>
                        </div>
                        <div class="recent-action">
                            <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&source=dashboard" class="btn-review">
                                Review
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No pending proposals for review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>