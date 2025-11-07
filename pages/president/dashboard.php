<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
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
    <div class="dashboard-cards">
        <div class="card">
            <h3>Pending Approval</h3>
            <p><?php echo $pending; ?></p>
            <a href="pending_proposals.php" class="btn">View Pending</a>
        </div>
        <div class="card">
            <h3>Approved Proposals</h3>
            <p><?php echo $approved; ?></p>
            <a href="approved_proposals.php" class="btn">View Approved</a>
        </div>
        <div class="card">
            <h3>Rejected Proposals</h3>
            <p><?php echo $rejected; ?></p>
            <a href="rejected_proposals.php" class="btn">View Rejected</a>
        </div>
        <div class="card">
            <h3>Total Proposals</h3>
            <p><?php echo $total; ?></p>
            <a href="all_proposals.php" class="btn">View All</a>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>