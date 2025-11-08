<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get all pending proposals for treasurer review
$pending_sql = "SELECT * FROM proposals WHERE status = 'pending' ORDER BY date_submitted DESC";
$pending_result = mysqli_query($conn, $pending_sql);
$pending_proposals = mysqli_fetch_all($pending_result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="header-with-back">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h1>Review Proposals</h1>
                <p>Budget review and financial assessment</p>
            </div>
        </div>
    </div>

    <div class="proposals-list">
        <?php if (!empty($pending_proposals)): ?>
            <?php foreach ($pending_proposals as $proposal): ?>
                <div class="proposal-review-card">
                    <div class="proposal-header">
                        <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                        <div class="proposal-meta">
                            <span class="budget">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                            <span class="status pending">Pending Review</span>
                        </div>
                    </div>
                    
                    <div class="proposal-details">
                        <div class="detail-row">
                            <div class="detail-group">
                                <strong>Event Date:</strong>
                                <span><?php echo $proposal['event_date'] ? date('M j, Y', strtotime($proposal['event_date'])) : 'Not set'; ?></span>
                            </div>
                            <div class="detail-group">
                                <strong>Venue:</strong>
                                <span><?php echo htmlspecialchars($proposal['venue']); ?></span>
                            </div>
                            <div class="detail-group">
                                <strong>Participants:</strong>
                                <span><?php echo $proposal['expected_participants']; ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-group">
                                <strong>Submitted By:</strong>
                                <span><?php echo htmlspecialchars($proposal['created_by']); ?></span>
                            </div>
                            <div class="detail-group">
                                <strong>Submitted On:</strong>
                                <span><?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($proposal['budget_breakdown'])): ?>
                    <div class="budget-section">
                        <h4>Budget Breakdown</h4>
                        <div class="budget-content">
                            <?php echo nl2br(htmlspecialchars($proposal['budget_breakdown'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($proposal['description'])): ?>
                    <div class="description-section">
                        <h4>Event Description</h4>
                        <p><?php echo htmlspecialchars($proposal['description']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="review-actions">
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-search-dollar"></i> Review Budget
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h3>All Caught Up!</h3>
                <p>No proposals awaiting budget review.</p>
                <a href="dashboard.php" class="btn btn-secondary">Return to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.proposals-list {
    margin-top: 20px;
}

.proposal-review-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #007bff;
}

.proposal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.proposal-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 20px;
    flex: 1;
    margin-right: 20px;
}

.proposal-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.budget {
    font-size: 18px;
    font-weight: 700;
    color: #28a745;
}

.status.pending {
    background: #fff3cd;
    color: #856404;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.proposal-details {
    margin-bottom: 20px;
}

.detail-row {
    display: flex;
    gap: 30px;
    margin-bottom: 10px;
}

.detail-group {
    flex: 1;
}

.detail-group strong {
    color: #495057;
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

.budget-section,
.description-section {
    margin-bottom: 20px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.budget-section h4,
.description-section h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 16px;
}

.budget-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 3px solid #007bff;
    white-space: pre-wrap;
}

.review-actions {
    text-align: right;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}
</style>

<?php include('../../includes/footer.php'); ?>