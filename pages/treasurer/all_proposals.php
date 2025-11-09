<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

$current_page = 'all';

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

    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo $status_counts['total']; ?></h3>
                <p>Total Proposals</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo $status_counts['pending']; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $status_counts['approved']; ?></h3>
                <p>Approved</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-info">
                <h3><?php echo $status_counts['rejected']; ?></h3>
                <p>Rejected</p>
            </div>
        </div>
    </div>

    <div class="proposals-grid">
        <?php if (!empty($all_proposals)): ?>
            <?php foreach ($all_proposals as $proposal): ?>
                <div class="proposal-card status-<?php echo $proposal['status']; ?>">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                        <span class="budget-badge">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                    </div>
                    
                    <div class="card-body">
                        <div class="proposal-meta">
                            <div class="meta-item">
                                <strong>Participants:</strong>
                                <span><?php echo $proposal['expected_participants']; ?> students</span>
                            </div>
                            <div class="meta-item">
                                <strong>Created by:</strong>
                                <span><?php echo htmlspecialchars($proposal['created_by']); ?></span>
                            </div>
                            <div class="meta-item">
                                <strong>Event Date:</strong>
                                <span><?php echo date('M j, Y', strtotime($proposal['event_date'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <strong>Status:</strong>
                                <span class="status-badge status-<?php echo $proposal['status']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $proposal['status'])); ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($proposal['budget_breakdown'])): ?>
                            <div class="detail-section">
                                <h4>Budget Breakdown</h4>
                                <p><?php echo htmlspecialchars($proposal['budget_breakdown']); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="detail-section">
                                <h4>Budget Breakdown</h4>
                                <div class="empty-detail">No breakdown provided</div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($proposal['objectives'])): ?>
                            <div class="detail-section">
                                <h4>Objectives</h4>
                                <p><?php echo htmlspecialchars($proposal['objectives']); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="detail-section">
                                <h4>Objectives</h4>
                                <div class="empty-detail">No objectives provided</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-actions">
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&source=<?php echo $current_page; ?>" class="btn btn-primary">
                            View Details
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