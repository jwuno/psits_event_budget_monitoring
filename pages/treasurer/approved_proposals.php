<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

$current_page = 'approved';

// Get approved proposals
$approved_sql = "SELECT * FROM proposals WHERE status = 'approved' ORDER BY review_date DESC";
$approved_result = mysqli_query($conn, $approved_sql);
$approved_proposals = mysqli_fetch_all($approved_result, MYSQLI_ASSOC);

// Total approved budget
$budget_sql = "SELECT SUM(proposed_budget) as total_approved FROM proposals WHERE status = 'approved'";
$budget_result = mysqli_query($conn, $budget_sql);
$budget_total = mysqli_fetch_assoc($budget_result)['total_approved'];
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Approved Proposals</h1>
            <p>Proposals with approved budgets</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <div class="stats-overview">
        <div class="stat-overview">
            <h3>Total Approved Budget</h3>
            <p class="stat-number">₱<?php echo number_format($budget_total, 2); ?></p>
        </div>
        <div class="stat-overview">
            <h3>Total Approved Proposals</h3>
            <p class="stat-number"><?php echo count($approved_proposals); ?></p>
        </div>
    </div>

    <div class="proposals-grid">
        <?php if (!empty($approved_proposals)): ?>
            <?php foreach ($approved_proposals as $proposal): ?>
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
                                <strong>Event Date:</strong>
                                <span><?php echo date('M j, Y', strtotime($proposal['event_date'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <strong>Approved by:</strong>
                                <span><?php echo $proposal['reviewed_by']; ?></span>
                            </div>
                            <div class="meta-item">
                                <strong>Approved on:</strong>
                                <span><?php echo date('M j, Y', strtotime($proposal['review_date'])); ?></span>
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

                        <?php if (!empty($proposal['budget_notes'])): ?>
                            <div class="detail-section">
                                <h4>Budget Notes</h4>
                                <p><?php echo htmlspecialchars($proposal['budget_notes']); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="detail-section">
                                <h4>Budget Notes</h4>
                                <div class="empty-detail">No notes provided</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-actions">
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&source=<?php echo $current_page; ?>" class="btn btn-secondary">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h4>No Approved Proposals</h4>
                <p>There are no approved proposals yet.</p>
                <a href="pending_reviews.php" class="btn btn-primary">Review Pending Proposals</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>