<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get all proposals
$all_sql = "SELECT * FROM proposals ORDER BY date_submitted DESC";
$all_result = mysqli_query($conn, $all_sql);
$all_proposals = mysqli_fetch_all($all_result, MYSQLI_ASSOC);

// Count by status
$status_sql = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
    FROM proposals";
$status_result = mysqli_query($conn, $status_sql);
$status_counts = mysqli_fetch_assoc($status_result);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>All Proposals</h1>
            <p>Complete overview of all proposals</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <div class="stats-overview">
        <div class="stat-overview">
            <h3>Total Proposals</h3>
            <p class="stat-number"><?php echo $status_counts['total']; ?></p>
        </div>
        <div class="stat-overview">
            <h3>Pending</h3>
            <p class="stat-number"><?php echo $status_counts['pending']; ?></p>
        </div>
        <div class="stat-overview">
            <h3>Approved</h3>
            <p class="stat-number"><?php echo $status_counts['approved']; ?></p>
        </div>
        <div class="stat-overview">
            <h3>Rejected</h3>
            <p class="stat-number"><?php echo $status_counts['rejected']; ?></p>
        </div>
    </div>

    <div class="proposals-list">
        <?php if (!empty($all_proposals)): ?>
            <?php foreach ($all_proposals as $proposal): ?>
                <div class="proposal-item status-<?php echo $proposal['status']; ?>">
                    <div class="item-main">
                        <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                        <div class="item-meta">
                            <span class="budget">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                            <span class="participants"><?php echo $proposal['expected_participants']; ?> participants</span>
                            <span class="creator">By: <?php echo htmlspecialchars($proposal['created_by']); ?></span>
                            <span class="date"><?php echo date('M j, Y', strtotime($proposal['date_submitted'])); ?></span>
                        </div>
                    </div>
                    <div class="item-status">
                        <span class="status-badge status-<?php echo $proposal['status']; ?>">
                            <?php echo ucfirst($proposal['status']); ?>
                        </span>
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn btn-sm">
                            View
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h4>No Proposals</h4>
                <p>There are no proposals in the system.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>