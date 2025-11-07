<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'student') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get only approved proposals
$sql = "SELECT * FROM proposals WHERE status = 'approved' ORDER BY date_submitted DESC";
$result = mysqli_query($conn, $sql);
$proposals = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Calculate total approved budget
$budget_sql = "SELECT SUM(proposed_budget) as total_budget FROM proposals WHERE status = 'approved'";
$budget_result = mysqli_query($conn, $budget_sql);
$total_budget = mysqli_fetch_assoc($budget_result)['total_budget'];
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="header-with-back">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h1>Approved Proposals</h1>
                <p>Proposals that have been approved for implementation</p>
            </div>
        </div>
    </div>

    <!-- Budget Summary -->
    <div class="budget-summary">
        <div class="summary-card">
            <h3>Total Approved Budget</h3>
            <div class="amount">₱<?php echo number_format($total_budget, 2); ?></div>
        </div>
        <div class="summary-card">
            <h3>Approved Proposals</h3>
            <div class="amount"><?php echo count($proposals); ?></div>
        </div>
    </div>

    <div class="proposals-grid">
        <?php if (!empty($proposals)): ?>
            <?php foreach ($proposals as $proposal): ?>
                <div class="proposal-card approved">
                    <div class="proposal-header">
                        <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                        <span class="status-badge status-approved">Approved</span>
                    </div>
                    
                    <div class="proposal-details">
                        <div class="detail-item">
                            <strong>Event Date:</strong>
                            <span><?php echo $proposal['event_date'] ? date('M j, Y', strtotime($proposal['event_date'])) : 'Not set'; ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Budget:</strong>
                            <span class="budget-amount">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Participants:</strong>
                            <span><?php echo $proposal['expected_participants']; ?> students</span>
                        </div>
                    </div>

                    <?php if (!empty($proposal['president_remarks'])): ?>
                        <div class="president-remarks">
                            <strong>President's Remarks:</strong>
                            <p><?php echo htmlspecialchars($proposal['president_remarks']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="proposal-meta">
                        <span class="submitted-by">By: <?php echo htmlspecialchars($proposal['created_by']); ?></span>
                        <span class="submitted-date">Approved on: <?php echo date('M j, Y', strtotime($proposal['updated_at'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No approved proposals</h3>
                <p>There are no approved proposals at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>