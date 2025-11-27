<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'treasurer') {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// =============== SUMMARY COUNTS =================

// proposals currently at treasurer stage (pending OR returned)
$pending_sql = "
    SELECT 
        SUM(CASE WHEN current_stage = 'treasurer' 
                  AND status IN ('pending','returned') THEN 1 ELSE 0 END) AS pending_treasurer,
        COUNT(*) AS total_proposals,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN status IN ('pending','returned') 
                  AND current_stage = 'treasurer' THEN proposed_budget ELSE 0 END) AS pending_budget,
        SUM(CASE WHEN status = 'approved' THEN proposed_budget ELSE 0 END) AS approved_budget
    FROM proposals
";
$counts_result = mysqli_query($conn, $pending_sql);
$counts = mysqli_fetch_assoc($counts_result);

// =============== RECENT PENDING / RETURNED FOR TREASURER =================

$recent_sql = "
    SELECT *
    FROM proposals
    WHERE current_stage = 'treasurer'
      AND status IN ('pending','returned')
    ORDER BY date_submitted DESC
    LIMIT 5
";
$recent_result = mysqli_query($conn, $recent_sql);
$recent_proposals = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);

include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Treasurer Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! Budget management and financial oversight.</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- ===== Stats Cards ===== -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo (int) ($counts['pending_treasurer'] ?? 0); ?></h3>
                <p>Pending Approval</p>
                <a href="pending_reviews.php" class="stat-link">Review Pending</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo (int) ($counts['total_proposals'] ?? 0); ?></h3>
                <p>Total Proposals</p>
                <a href="all_proposals.php" class="stat-link">View All</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo (int) ($counts['approved'] ?? 0); ?></h3>
                <p>Approved Proposals</p>
                <a href="approved_proposals.php" class="stat-link">View Approved</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-info">
                <h3><?php echo (int) ($counts['rejected'] ?? 0); ?></h3>
                <p>Rejected Proposals</p>
                <a href="rejected_proposals.php" class="stat-link">View Rejected</a>
            </div>
        </div>
    </div>

    <!-- Budget summary -->
    <div class="budget-cards">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($counts['pending_budget'] ?? 0, 2); ?></h3>
                <p>Pending Budget</p>
                <span class="stat-sub">Awaiting approval</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($counts['approved_budget'] ?? 0, 2); ?></h3>
                <p>Approved Budget</p>
                <span class="stat-sub">Total allocated</span>
            </div>
        </div>
    </div>

    <!-- ===== Recent Pending Proposals (including returned by President) ===== -->
    <div class="card">
        <div class="card-header">
            <h2>Recent Pending Proposals</h2>
            <a href="pending_reviews.php" class="btn-link">View All</a>
        </div>

        <?php if (empty($recent_proposals)): ?>
            <div class="empty-state">
                <p>No pending proposals for review.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>From</th>
                            <th>Status @Treasurer</th>
                            <th>Budget</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_proposals as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($p['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($p['created_by']); ?></td>
                                <td>
                                    <?php if ($p['status'] === 'returned' && $p['returned_from'] === 'president'): ?>
                                        <span class="status-badge status-returned">Returned by President</span>
                                    <?php elseif ($p['status'] === 'pending'): ?>
                                        <span class="status-badge status-pending">From Secretary</span>
                                    <?php else: ?>
                                        <span class="status-badge status-<?php echo strtolower($p['status']); ?>">
                                            <?php echo ucfirst($p['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                                <td>
                                    <a href="pending_reviews.php" class="btn btn-view">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
