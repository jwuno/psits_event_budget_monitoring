<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// DEBUG: Check proposal statuses
$debug_sql = "SELECT id, title, status, current_stage, treasurer_status FROM proposals ORDER BY date_submitted DESC LIMIT 5";
$debug_result = mysqli_query($conn, $debug_sql);
$debug_proposals = mysqli_fetch_all($debug_result, MYSQLI_ASSOC);

echo "<!-- DEBUG: Recent Proposals -->";
echo "<!-- ";
foreach($debug_proposals as $proposal) {
    echo "ID: " . $proposal['id'] . " | Title: " . $proposal['title'] . " | Status: " . $proposal['status'] . " | Stage: " . $proposal['current_stage'] . " | Treasurer: " . $proposal['treasurer_status'] . " \\n";
}
echo "-->";


// Get counts for dashboard
$counts_sql = "SELECT 
    COUNT(*) as total_proposals,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
    SUM(CASE WHEN status = 'pending' THEN proposed_budget ELSE 0 END) as pending_budget,
    SUM(CASE WHEN status = 'approved' THEN proposed_budget ELSE 0 END) as approved_budget
    FROM proposals";
$counts_result = mysqli_query($conn, $counts_sql);
$counts = mysqli_fetch_assoc($counts_result);

// Get recent pending proposals
$recent_sql = "SELECT * FROM proposals WHERE status = 'pending' ORDER BY date_submitted DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_sql);
$recent_proposals = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Treasurer Dashboard</h1>
        <p>Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>! Budget management and financial oversight.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-cards">
        <div class="card">
            <h3>Pending Approval</h3>
            <p class="count"><?php echo $counts['pending']; ?></p>
            <p class="card-desc">Awaiting budget review</p>
            <a href="pending_reviews.php" class="btn">Review Pending</a>
        </div>
        
        <div class="card">
            <h3>Total Proposals</h3>
            <p class="count"><?php echo $counts['total_proposals']; ?></p>
            <p class="card-desc">All proposals in system</p>
            <a href="all_proposals.php" class="btn">View All</a>
        </div>
        
        <div class="card">
            <h3>Approved</h3>
            <p class="count"><?php echo $counts['approved']; ?></p>
            <p class="card-desc">Budget approved</p>
            <a href="approved_proposals.php" class="btn">View Approved</a>
        </div>
        
        <div class="card">
            <h3>Rejected</h3>
            <p class="count"><?php echo $counts['rejected']; ?></p>
            <p class="card-desc">Budget rejected</p>
            <a href="rejected_proposals.php" class="btn">View Rejected</a>
        </div>
    </div>

    <!-- Budget Overview Cards -->
    <div class="dashboard-cards">
        <div class="card">
            <h3>Pending Budget</h3>
            <p class="count">₱<?php echo number_format($counts['pending_budget'], 2); ?></p>
            <p class="card-desc">Awaiting approval</p>
        </div>
        
        <div class="card">
            <h3>Approved Budget</h3>
            <p class="count">₱<?php echo number_format($counts['approved_budget'], 2); ?></p>
            <p class="card-desc">Total allocated</p>
        </div>
    </div>

    <!-- Recent Pending Proposals Table -->
    <div class="recent-submissions">
        <div class="section-header">
            <h2>Recent Pending Reviews</h2>
            <a href="pending_reviews.php" class="btn-view-all">View All</a>
        </div>
        
        <?php if (!empty($recent_proposals)): ?>
        <div class="table-container">
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>Proposal</th>
                        <th>Budget</th>
                        <th>Participants</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_proposals as $proposal): ?>
                    <tr>
                        <td class="proposal-title"><?php echo htmlspecialchars($proposal['title']); ?></td>
                        <td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td>
                        <td><?php echo $proposal['expected_participants']; ?> students</td>
                        <td><?php echo date('M j, Y', strtotime($proposal['date_submitted'])); ?></td>
                        <td>
                            <a href="review_proposal.php?id=<?php echo $proposal['id']; ?>&return=dashboard.php" class="btn-action">
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
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>