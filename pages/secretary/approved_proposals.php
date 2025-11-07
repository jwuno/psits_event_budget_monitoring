<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');

// Get approved proposals by this secretary
$query = "SELECT * FROM proposals WHERE created_by = '".$_SESSION['full_name']."' AND status='Approved' ORDER BY date_submitted DESC";
$result = mysqli_query($conn, $query);
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Approved Proposals</h1>
        <p>Your proposals that have been approved</p>
    </div>

    <?php if(mysqli_num_rows($result) > 0): ?>
    <div class="table-container">
        <table class="submissions-table">
            <thead>
                <tr>
                    <th>Proposal Title</th>
                    <th>Date Submitted</th>
                    <th>Date Approved</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($proposal = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></td>
                    <td>
                        <a href="view_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn-action">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <h3>No Approved Proposals</h3>
        <p>You don't have any approved proposals yet.</p>
    </div>
    <?php endif; ?>

    <div class="navigation-actions">
        <a href="dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>