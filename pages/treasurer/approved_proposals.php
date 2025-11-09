<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

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
    <div class="page-header">
        <h1>Approved Proposals</h1>
        <p>Proposals with approved budgets</p>
    </div>

    <?php if (!empty($approved_proposals)): ?>
    <div class="table-container">
        <table class="submissions-table">
            <thead>
                <tr>
                    <th>Proposal Title</th>
                    <th>Budget</th>
                    <th>Participants</th>
                    <th>Approved By</th>
                    <th>Date Approved</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approved_proposals as $proposal): ?>
                <tr>
                    <td class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></td>
                    <td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td>
                    <td><?php echo $proposal['expected_participants']; ?> students</td>
                    <td><?php echo $proposal['reviewed_by']; ?></td>
                    <td><?php echo date('M j, Y', strtotime($proposal['review_date'])); ?></td>
                    <td>
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&return=approved_proposals.php" class="btn-action">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <h3>No Approved Proposals</h3>
        <p>There are no approved proposals yet.</p>
        <a href="pending_reviews.php" class="btn">Review Pending Proposals</a>
    </div>
    <?php endif; ?>

    <div class="navigation-actions">
        <a href="dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>