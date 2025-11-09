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
    <div class="page-header">
        <h1>Pending Reviews</h1>
        <p>Proposals awaiting your budget approval</p>
    </div>

    <?php if (!empty($pending_proposals)): ?>
    <div class="table-container">
        <table class="submissions-table">
            <thead>
                <tr>
                    <th>Proposal Title</th>
                    <th>Budget</th>
                    <th>Participants</th>
                    <th>Created By</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_proposals as $proposal): ?>
                <tr>
                    <td class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></td>
                    <td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td>
                    <td><?php echo $proposal['expected_participants']; ?> students</td>
                    <td><?php echo htmlspecialchars($proposal['created_by']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($proposal['date_submitted'])); ?></td>
                    <td>
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&return=pending_reviews.php" class="btn-action">
                            <i class="fas fa-eye"></i> Review
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
        <h3>All Caught Up!</h3>
        <p>No proposals awaiting budget review.</p>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
    <?php endif; ?>

    <div class="navigation-actions">
        <a href="dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>