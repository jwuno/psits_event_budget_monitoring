<?php
require_once '../../includes/auth.php';
requireRole('adviser');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

/* ---------- STATS ---------- */

$pendingFinal = 0;
$res1 = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c
     FROM proposals
     WHERE status = 'pending'
       AND current_stage = 'adviser'"
);
if ($res1 && $row1 = mysqli_fetch_assoc($res1)) {
    $pendingFinal = (int)$row1['c'];
}

$finalApproved = 0;
$finalRejected = 0;

$res2 = mysqli_query(
    $conn,
    "SELECT adviser_status, COUNT(*) AS c
     FROM proposals
     WHERE current_stage = 'final'
     GROUP BY adviser_status"
);
if ($res2) {
    while ($row2 = mysqli_fetch_assoc($res2)) {
        $s = strtolower($row2['adviser_status']);
        if ($s === 'approved') {
            $finalApproved = (int)$row2['c'];
        } elseif ($s === 'rejected') {
            $finalRejected = (int)$row2['c'];
        }
    }
}

$totalApprovedBudget = 0;
$res3 = mysqli_query(
    $conn,
    "SELECT SUM(proposed_budget) AS total
     FROM proposals
     WHERE status = 'approved'"
);
if ($res3 && $row3 = mysqli_fetch_assoc($res3)) {
    $totalApprovedBudget = (float)$row3['total'];
}

/* ---------- TABLES ---------- */

$forDecision = mysqli_query(
    $conn,
    "SELECT id, title, event_date, venue, proposed_budget
     FROM proposals
     WHERE status = 'pending'
       AND current_stage = 'adviser'
     ORDER BY date_submitted ASC"
);

$history = mysqli_query(
    $conn,
    "SELECT id, title, event_date, proposed_budget, adviser_status, review_date
     FROM proposals
     WHERE current_stage = 'final'
     ORDER BY review_date DESC
     LIMIT 10"
);
?>
<div class="dashboard">
    <div class="dashboard-header">
        <h1>Adviser Dashboard</h1>
        <p>Review organization-endorsed proposals and issue final approval or rejection.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card pending">
            <span class="stat-label">Pending Final Decision</span>
            <span class="stat-value"><?php echo $pendingFinal; ?></span>
        </div>
        <div class="stat-card approved">
            <span class="stat-label">Final Approved</span>
            <span class="stat-value"><?php echo $finalApproved; ?></span>
        </div>
        <div class="stat-card rejected">
            <span class="stat-label">Final Rejected</span>
            <span class="stat-value"><?php echo $finalRejected; ?></span>
        </div>
        <div class="stat-card budget">
            <span class="stat-label">Total Approved Budget</span>
            <span class="stat-value">₱<?php echo number_format($totalApprovedBudget, 2); ?></span>
        </div>
    </div>

    <div class="charts-grid">
        <div class="card">
            <h2>Proposals Awaiting Final Decision</h2>
            <div class="table-wrapper">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Venue</th>
                            <th>Budget</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($forDecision && mysqli_num_rows($forDecision) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($forDecision)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($p['venue']); ?></td>
                                <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                                <td>
                                    <a href="review_proposal.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-primary">
                                        Decide
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">No proposals waiting for final decision.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2>Recent Final Decisions</h2>
            <div class="table-wrapper">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Budget</th>
                            <th>Adviser Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($history && mysqli_num_rows($history) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($history)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['event_date']); ?></td>
                                <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($p['adviser_status'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">No final decisions recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
