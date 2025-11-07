<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');

// Get proposals created by this secretary
$query = "SELECT * FROM proposals WHERE created_by = '".$_SESSION['full_name']."' ORDER BY date_submitted DESC";
$result = mysqli_query($conn, $query);
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>My Proposals</h1>
        <p>All proposals I have submitted</p>
    </div>

    <?php if(mysqli_num_rows($result) > 0): ?>
    <div class="table-container">
        <table class="submissions-table">
            <thead>
                <tr>
                    <th>Proposal Title</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($proposal = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($proposal['status']); ?>">
                            <?php echo $proposal['status']; ?>
                        </span>
                    </td>
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
        <i class="fas fa-inbox"></i>
        <h3>No Proposals Yet</h3>
        <p>You haven't submitted any proposals yet.</p>
        <a href="create_proposal.php" class="btn">Create Your First Proposal</a>
    </div>
    <?php endif; ?>

    <div class="navigation-actions">
        <a href="dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>