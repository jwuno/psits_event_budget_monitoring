<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'adviser') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get proposals currently at ADVISER stage
$sql = "SELECT * FROM proposals 
        WHERE status = 'pending'
          AND current_stage = 'adviser'
        ORDER BY date_submitted DESC";

$result = mysqli_query($conn, $sql);

include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Pending Proposals</h1>
            <p>Proposals awaiting your final approval or rejection.</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Pending Proposals</h2>
            <p>Review and finalize these proposals.</p>
        </div>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <div class="empty-state">
                <p>✅ All caught up! No proposals waiting for your review.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Created By</th>
                            <th>Submitted</th>
                            <th>Budget</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($row['date_submitted'])); ?></td>
                                <td>₱<?php echo number_format($row['proposed_budget'], 2); ?></td>
                                <td>
                                    <a href="review_proposal.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-view">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
