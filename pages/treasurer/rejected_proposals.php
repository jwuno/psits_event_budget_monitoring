<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get rejected proposals
$rejected_sql = "SELECT * FROM proposals WHERE status = 'rejected' ORDER BY review_date DESC";
$rejected_result = mysqli_query($conn, $rejected_sql);
$rejected_proposals = mysqli_fetch_all($rejected_result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Rejected Proposals</h1>
        <p>Proposals with rejected budgets</p>
    </div>

    <?php if (!empty($rejected_proposals)): ?>
    <div class="table-container">
        <table class="submissions-table">
            <thead>
                <tr>
                    <th>Proposal Title</th>
                    <th>Budget</th>
                    <th>Participants</th>
                    <th>Rejected By</th>
                    <th>Date Rejected</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rejected_proposals as $proposal): ?>
                <tr>
                    <td class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></td>
                    <td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td>
                    <td><?php echo $proposal['expected_participants']; ?> students</td>
                    <td><?php echo $proposal['reviewed_by']; ?></td>
                    <td><?php echo date('M j, Y', strtotime($proposal['review_date'])); ?></td>
                    <td>
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&return=rejected_proposals.php" class="btn-action">
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
        <h3>No Rejected Proposals</h3>
        <p>There are no rejected proposals.</p>
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