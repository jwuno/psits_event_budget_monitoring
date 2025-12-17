<?php
require_once '../../includes/auth.php';
requireRole('president');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

/* ---------- STATS ---------- */

$pendingPres = 0;
$res1 = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c
     FROM proposals
     WHERE status = 'pending'
       AND current_stage = 'president'"
);
if ($res1 && $row1 = mysqli_fetch_assoc($res1)) {
    $pendingPres = (int)$row1['c'];
}

$returnedToTreas = 0;
$res2 = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c
     FROM proposals
     WHERE status = 'returned'
       AND current_stage = 'treasurer'"
);
if ($res2 && $row2 = mysqli_fetch_assoc($res2)) {
    $returnedToTreas = (int)$row2['c'];
}

$forwardedToAdviser = 0;
$res3 = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c
     FROM proposals
     WHERE president_status = 'approved'"
);
if ($res3 && $row3 = mysqli_fetch_assoc($res3)) {
    $forwardedToAdviser = (int)$row3['c'];
}

$totalApprovedBudget = 0;
$res4 = mysqli_query(
    $conn,
    "SELECT SUM(proposed_budget) AS total
     FROM proposals
     WHERE status = 'approved'"
);
if ($res4 && $row4 = mysqli_fetch_assoc($res4)) {
    $totalApprovedBudget = (float)$row4['total'];
}

/* ---------- TABLES ---------- */

// Proposals waiting for President review
$forReview = mysqli_query(
    $conn,
    "SELECT id, title, event_date, venue, proposed_budget
     FROM proposals
     WHERE status = 'pending'
       AND current_stage = 'president'
     ORDER BY date_submitted DESC"
);

// Recently processed by President (shows combined status)
$recent = mysqli_query(
    $conn,
    "SELECT id,
            title,
            event_date,
            proposed_budget,
            president_status,
            adviser_status,
            review_date
     FROM proposals
     WHERE president_status <> 'pending'
     ORDER BY review_date DESC
     LIMIT 8"
);
?>
<div class="dashboard">
    <div class="dashboard-header">
        <h1>President Dashboard</h1>
        <p>Review Treasurer-endorsed proposals and forward qualified events to the Adviser.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card pending">
            <span class="stat-label">Pending for President Review</span>
            <span class="stat-value"><?php echo $pendingPres; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Returned to Treasurer</span>
            <span class="stat-value"><?php echo $returnedToTreas; ?></span>
        </div>
        <div class="stat-card approved">
            <span class="stat-label">Forwarded to Adviser</span>
            <span class="stat-value"><?php echo $forwardedToAdviser; ?></span>
        </div>
        <div class="stat-card budget">
            <span class="stat-label">Total Final Approved Budget</span>
            <span class="stat-value">₱<?php echo number_format($totalApprovedBudget, 2); ?></span>
        </div>
    </div>

    <div class="charts-grid">
        <!-- Pending for President -->
        <div class="card">
            <h2>Proposals Awaiting Your Review</h2>
            <div class="table-wrapper table-scroll">
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
                    <?php if ($forReview && mysqli_num_rows($forReview) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($forReview)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($p['venue']); ?></td>
                                <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                                <td class="actions-cell">
                                    <a href="review_proposal.php?id=<?php echo (int)$p['id']; ?>"
                                       class="btn btn-sm btn-primary">
                                        Review
                                    </a>
                                    <!-- Anything here is waiting on the President -->
                                    <span class="attention-flag"
                                          title="Awaiting your review">!</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">No proposals waiting for President right now.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top:0.5rem;font-size:0.8rem;color:#6b7280;">
                <span class="attention-flag"
                      style="vertical-align:middle;margin-right:0.25rem;">!</span>
                indicates a proposal <strong>awaiting your review</strong>.
            </p>
        </div>

        <!-- Recent actions -->
        <div class="card">
            <h2>Recently Processed by President</h2>
            <div class="table-wrapper table-scroll">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($recent && mysqli_num_rows($recent) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($recent)): ?>
                            <?php
                                // Prefer final Adviser status if available; else show President status
                                $statusRaw = !empty($p['adviser_status'])
                                    ? $p['adviser_status']
                                    : $p['president_status'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['event_date']); ?></td>
                                <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                                <td>
                                    <?php echo renderStatusPill($statusRaw); ?>
                                </td>
                                <td>
                                    <a href="view_proposal.php?id=<?php echo (int)$p['id']; ?>"
                                       class="btn btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">No recent actions yet.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
