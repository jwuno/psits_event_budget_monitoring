<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');

// Get all proposals
$query = "SELECT * FROM proposals ORDER BY date_submitted DESC";
$result = mysqli_query($conn, $query);
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>All Proposals</h1>
        <p>Complete list of all event proposals</p>
    </div>

    <?php if(mysqli_num_rows($result) > 0): ?>
    <div class="proposals-grid">
        <?php while($proposal = mysqli_fetch_assoc($result)): ?>
        <div class="proposal-card">
            <div class="proposal-header">
                <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                <span class="status <?php echo strtolower($proposal['status']); ?>">
                    <?php echo $proposal['status']; ?>
                </span>
            </div>
            
            <div class="proposal-details">
                <p><strong>Proposed by:</strong> <?php echo htmlspecialchars($proposal['created_by']); ?></p>
                <p><strong>Date Submitted:</strong> 
                    <?php 
                    if (isset($proposal['date_submitted']) && !empty($proposal['date_submitted'])) {
                        echo date('M j, Y', strtotime($proposal['date_submitted']));
                    } else {
                        echo 'Not set';
                    }
                    ?>
                </p>
                <p><strong>Description:</strong> 
                    <?php 
                    if (isset($proposal['description'])) {
                        echo substr($proposal['description'], 0, 100) . '...';
                    } else {
                        echo 'No description';
                    }
                    ?>
                </p>
            </div>
            
            <div class="proposal-actions">
                <a href="view_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn btn-view">
                    <i class="fas fa-eye"></i> View Details
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No Proposals</h3>
        <p>There are no proposals in the system yet.</p>
    </div>
    <?php endif; ?>

    <div class="navigation-actions">
        <a href="dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>