<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'student') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get all proposals from database
$sql = "SELECT * FROM proposals ORDER BY date_submitted DESC";
$result = mysqli_query($conn, $sql);
$proposals = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="header-with-back">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h1>All Proposals</h1>
                <p>View all submitted proposals in the system</p>
            </div>
        </div>
    </div>

    <div class="proposals-grid">
        <?php if (!empty($proposals)): ?>
            <?php foreach ($proposals as $proposal): ?>
                <div class="proposal-card">
                    <div class="proposal-header">
                        <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                        <span class="status-badge status-<?php echo strtolower($proposal['status']); ?>">
                            <?php echo $proposal['status']; ?>
                        </span>
                    </div>
                    
                    <div class="proposal-details">
                        <div class="detail-item">
                            <strong>Event Date:</strong>
                            <span><?php echo $proposal['event_date'] ? date('M j, Y', strtotime($proposal['event_date'])) : 'Not set'; ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Venue:</strong>
                            <span><?php echo htmlspecialchars($proposal['venue']); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Budget:</strong>
                            <span>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Participants:</strong>
                            <span><?php echo $proposal['expected_participants']; ?> expected</span>
                        </div>
                    </div>

                    <div class="proposal-meta">
                        <span class="submitted-by">By: <?php echo htmlspecialchars($proposal['created_by']); ?></span>
                        <span class="submitted-date"><?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No proposals found</h3>
                <p>There are no proposals in the system yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>