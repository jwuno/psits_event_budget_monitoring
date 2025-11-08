<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get all pending proposals from database
$pending_sql = "SELECT * FROM proposals WHERE status = 'pending' ORDER BY date_submitted DESC";
$pending_result = mysqli_query($conn, $pending_sql);
$pending_proposals = mysqli_fetch_all($pending_result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Pending Reviews</h1>
            <p>Proposals awaiting your budget approval</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <div class="proposals-grid">
        <?php if (!empty($pending_proposals)): ?>
            <?php foreach ($pending_proposals as $proposal): ?>
                <div class="proposal-card">
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
                        </div>

                        <?php if (!empty($proposal['budget_breakdown'])): ?>
                            <div class="detail-section">
                                <h4>Budget Breakdown</h4>
                                <p><?php echo htmlspecialchars($proposal['budget_breakdown']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($proposal['objectives'])): ?>
                            <div class="detail-section">
                                <h4>Objectives</h4>
                                <p><?php echo htmlspecialchars($proposal['objectives']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-actions">
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn btn-primary">
                            Review Budget
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h4>All Caught Up!</h4>
                <p>No proposals awaiting budget review.</p>
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>