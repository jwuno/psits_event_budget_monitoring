<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../login.php");
    exit;
}
include('../../includes/db.php');

// Counts
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE status='budget_approved'"))['total'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE status='president_approved'"))['total'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals WHERE status='rejected'"))['total'];
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM proposals"))['total'];
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>President Dashboard</h1>
    </div>

    <div class="dashboard-cards">
        <div class="card">
            <h3>Pending Approval</h3>
            <p><?php echo $pending; ?></p>
            <a href="pending_proposals.php" class="btn">View</a>
        </div>
        <div class="card">
            <h3>Approved Proposals</h3>
            <p><?php echo $approved; ?></p>
            <a href="approved_proposals.php" class="btn">View</a>
        </div>
        <div class="card">
            <h3>Rejected Proposals</h3>
            <p><?php echo $rejected; ?></p>
            <a href="rejected_proposals.php" class="btn">View</a>
        </div>
        <div class="card">
            <h3>Total Proposals</h3>
            <p><?php echo $total; ?></p>
            <a href="all_proposals.php" class="btn">View</a>
        </div>
    </div>
</div>
