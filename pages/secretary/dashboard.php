<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');

// Counts for secretary - ONLY their proposals
$my_proposals_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE created_by = '".$_SESSION['full_name']."'");
$pending_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE created_by = '".$_SESSION['full_name']."' AND status='Pending'");
$approved_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE created_by = '".$_SESSION['full_name']."' AND status='Approved'");

$my_proposals = $my_proposals_result ? mysqli_fetch_assoc($my_proposals_result)['total'] : 0;
$pending = $pending_result ? mysqli_fetch_assoc($pending_result)['total'] : 0;
$approved = $approved_result ? mysqli_fetch_assoc($approved_result)['total'] : 0;

// Get recent submissions
$recent_query = "SELECT * FROM proposals WHERE created_by = '".$_SESSION['full_name']."' ORDER BY date_submitted DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);
?>

<div class="dashboard-container">
    <!-- Statistics Cards -->
    <div class="dashboard-cards">
    <div class="card">
        <h3>Total Proposals Created</h3>
        <p class="count"><?php echo $my_proposals; ?></p>
        <p class="card-desc">Proposals you've submitted</p>
        <a href="my_proposals.php" class="btn">View Proposals</a>
    </div>
    
    <div class="card">
        <h3>Pending Submissions</h3>
        <p class="count"><?php echo $pending; ?></p>
        <p class="card-desc">Awaiting approval</p>
        <a href="pending_proposals.php" class="btn">Track Submissions</a>
    </div>
    
    <div class="card">
        <h3>Approved</h3>
        <p class="count"><?php echo $approved; ?></p>
        <p class="card-desc">Approved proposals</p>
        <a href="approved_proposals.php" class="btn">View Approved</a>
    </div>
    
    <div class="card">
        <h3>Create New</h3>
        <p class="count">+</p>
        <p class="card-desc">Submit a new proposal</p>
        <a href="create_proposal.php" class="btn">Create Proposal</a>
    </div>
</div>

    <!-- Recent Submissions Table -->
    <div class="recent-submissions">
        <div class="section-header">
            <h2>Recent Submissions</h2>
            <a href="my_proposals.php" class="btn-view-all">View All</a>
        </div>
        
        <?php if(mysqli_num_rows($recent_result) > 0): ?>
        <div class="table-container">
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>Proposal</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($proposal = mysqli_fetch_assoc($recent_result)): ?>
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
            <h3>No Submissions Yet</h3>
            <p>You haven't submitted any proposals yet.</p>
            <a href="create_proposal.php" class="btn">Create Your First Proposal</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>