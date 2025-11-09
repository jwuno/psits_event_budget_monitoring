<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

// Load president-specific CSS
echo '<link rel="stylesheet" href="/psits_event_budget_monitoring/assets/css/president-dashboard.css">';

include('../../includes/db.php');

// Counts - using your actual status values
$pending_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE status='Pending'");
$approved_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE status='Approved'");
$rejected_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE status='Rejected'");
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals");

$pending = $pending_result ? mysqli_fetch_assoc($pending_result)['total'] : 0;
$approved = $approved_result ? mysqli_fetch_assoc($approved_result)['total'] : 0;
$rejected = $rejected_result ? mysqli_fetch_assoc($rejected_result)['total'] : 0;
$total = $total_result ? mysqli_fetch_assoc($total_result)['total'] : 0;
?>

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>President Dashboard</h1>
            <p>Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>! Overview of all proposals and approvals.</p>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo $pending; ?></h3>
                <p>Pending Approval</p>
                <a href="pending_proposals.php" class="stat-link">Review Pending</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo $total; ?></h3>
                <p>Total Proposals</p>
                <a href="all_proposals.php" class="stat-link">View All</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $approved; ?></h3>
                <p>Approved Proposals</p>
                <a href="approved_proposals.php" class="stat-link">View Approved</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-info">
                <h3><?php echo $rejected; ?></h3>
                <p>Rejected Proposals</p>
                <a href="rejected_proposals.php" class="stat-link">View Rejected</a>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>