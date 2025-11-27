<?php
// pages/president/dashboard.php

session_start();

// Only presidents can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'president') {
    header("Location: /psits_event_budget_monitoring/index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// ---------- STATS QUERIES ----------

// Pending proposals waiting for the President's decision
$pending_sql = "
    SELECT COUNT(*) AS cnt 
    FROM proposals 
    WHERE status = 'pending' 
      AND current_stage = 'president'
";
$pending_result = $conn->query($pending_sql);
$pending_count = ($pending_result && $row = $pending_result->fetch_assoc()) ? (int)$row['cnt'] : 0;

// Approved proposals (president_status = approved)
$approved_sql = "
    SELECT COUNT(*) AS cnt 
    FROM proposals 
    WHERE president_status = 'approved'
";
$approved_result = $conn->query($approved_sql);
$approved_count = ($approved_result && $row = $approved_result->fetch_assoc()) ? (int)$row['cnt'] : 0;

// Rejected proposals (president_status = rejected)
$rejected_sql = "
    SELECT COUNT(*) AS cnt 
    FROM proposals 
    WHERE president_status = 'rejected'
";
$rejected_result = $conn->query($rejected_sql);
$rejected_count = ($rejected_result && $row = $rejected_result->fetch_assoc()) ? (int)$row['cnt'] : 0;

// Total proposals (for overview)
$total_sql = "SELECT COUNT(*) AS cnt FROM proposals";
$total_result = $conn->query($total_sql);
$total_count = ($total_result && $row = $total_result->fetch_assoc()) ? (int)$row['cnt'] : 0;

include '../../includes/header.php';
?>

<div class="dashboard-container">

    <!-- Top welcome card (same style concept as Adviser) -->
    <div class="card" style="margin-bottom: 25px;">
        <h1>President Dashboard</h1>
        <p>
            Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>!
            Here’s an overview of proposals awaiting your review and those you’ve already acted on.
        </p>
    </div>

    <!-- Stat cards row -->
    <div class="stats-cards">

        <!-- Pending for President -->
        <div class="card stat-card">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $pending_count; ?></h3>
                <p>Pending Approval</p>
                <a href="pending_proposals.php" class="btn btn-primary" style="margin-top:8px;">View Pending</a>
            </div>
        </div>

        <!-- Total proposals -->
        <div class="card stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_count; ?></h3>
                <p>Total Proposals</p>
                <a href="all_proposals.php" class="btn btn-secondary" style="margin-top:8px;">View All</a>
            </div>
        </div>

        <!-- Approved by President -->
        <div class="card stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $approved_count; ?></h3>
                <p>Approved Proposals</p>
                <a href="approved_proposals.php" class="btn btn-primary" style="margin-top:8px;">View Approved</a>
            </div>
        </div>

        <!-- Rejected by President -->
        <div class="card stat-card">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $rejected_count; ?></h3>
                <p>Rejected Proposals</p>
                <a href="rejected_proposals.php" class="btn btn-danger" style="margin-top:8px;">View Rejected</a>
            </div>
        </div>

    </div>

    <!-- Optional: small helper text like Adviser page -->
    <div class="card" style="margin-top: 25px;">
        <h2>Proposals Awaiting Your Decision</h2>
        <p class="card-desc">
            These proposals have already passed the Treasurer and are now routed to your office
            for organizational approval. Use the <strong>Pending Approval</strong> card above to review them.
        </p>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
