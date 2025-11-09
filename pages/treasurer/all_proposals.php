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
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>All Proposals</h1>
        <p>Complete overview of all proposals</p>
    </div>

    <?php if (!empty($all_proposals)): ?>
    <div class="table-container">
        <table class="submissions-table">
            <thead>
                <tr>
                    <th>Proposal Title</th>
                    <th>Budget</th>
                    <th>Participants</th>
                    <th>Created By</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_proposals as $proposal): ?>
                <tr>
                    <td class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></td>
                    <td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td>
                    <td><?php echo $proposal['expected_participants']; ?> students</td>
                    <td><?php echo htmlspecialchars($proposal['created_by']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($proposal['date_submitted'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($proposal['status']); ?>">
                            <?php echo ucfirst($proposal['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&return=all_proposals.php" class="btn-action">
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
        <i class="fas fa-inbox"></i>
        <h3>No Proposals</h3>
        <p>There are no proposals in the system.</p>
    </div>
    <?php endif; ?>

    <div class="navigation-actions">
        <a href="dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>