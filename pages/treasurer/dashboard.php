<?php
require_once '../../includes/auth.php';
requireRole('treasurer');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

/* ---------------------------
   COUNTS FOR SUMMARY CARDS
   --------------------------- */

// 1. Pending for Treasurer review (includes returned from President)
$pendingSql = "
    SELECT *
    FROM proposals
    WHERE current_stage = 'treasurer'
      AND status IN ('pending', 'returned')
    ORDER BY date_submitted DESC
";
$pendingRes = mysqli_query($conn, $pendingSql);
$pendingCount = $pendingRes ? mysqli_num_rows($pendingRes) : 0;

// 2. Returned to Secretary (i.e., Treasurer sent them back)
$returnedToSecCount = 0;
$r2 = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c
     FROM proposals
     WHERE status = 'returned'
       AND returned_from = 'treasurer'"
);
if ($r2 && $row = mysqli_fetch_assoc($r2)) {
    $returnedToSecCount = (int)$row['c'];
}

// 3. Endorsed to President (Treasurer approved & forwarded)
$endorsedCount = 0;
$r3 = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c
     FROM proposals
     WHERE treasurer_status = 'approved'
       AND current_stage IN ('president', 'adviser', 'final')"
);
if ($r3 && $row = mysqli_fetch_assoc($r3)) {
    $endorsedCount = (int)$row['c'];
}

// 4. Total final approved budget (org-wide)
$totalFinalBudget = 0;
$r4 = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(proposed_budget),0) AS total_budget
     FROM proposals
     WHERE status = 'approved'"
);
if ($r4 && $row = mysqli_fetch_assoc($r4)) {
    $totalFinalBudget = (float)$row['total_budget'];
}

/* ---------------------------
   RECENTLY PROCESSED BY TREASURER
   --------------------------- */

$recentSql = "
    SELECT *
    FROM proposals
    WHERE treasurer_status IN ('approved', 'returned')
    ORDER BY review_date DESC
    LIMIT 5
";
$recentRes = mysqli_query($conn, $recentSql);
?>

<div class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Treasurer Dashboard</h1>
            <p>Review budgets and endorse eligible proposals to the President.</p>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Pending for Treasurer Review</span>
            <span class="stat-value"><?php echo $pendingCount; ?></span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Returned to Secretary</span>
            <span class="stat-value"><?php echo $returnedToSecCount; ?></span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Endorsed to President</span>
            <span class="stat-value"><?php echo $endorsedCount; ?></span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Total Final Approved Budget</span>
            <span class="stat-value">₱<?php echo number_format($totalFinalBudget, 2); ?></span>
        </div>
    </div>

    <!-- Proposals awaiting Treasurer review (includes returned from President) -->
    <div class="card">
        <h2>Proposals Awaiting Your Review</h2>
        <div class="table-wrapper">
            <table class="proposals-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>From</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($pendingCount === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;color:#6b7280;">
                            No proposals waiting for Treasurer right now.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($pendingRes)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo htmlspecialchars($p['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($p['venue']); ?></td>
                            <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($p['status'])); ?></td>
                            <td>
                                <?php
                                // Who sent it here?
                                echo $p['returned_from']
                                     ? 'Returned by ' . ucfirst(htmlspecialchars($p['returned_from']))
                                     : 'New from Secretary';
                                ?>
                            </td>
                            <td>
                                <a href="review_proposal.php?id=<?php echo (int)$p['id']; ?>"
                                   class="btn btn-sm btn-primary">
                                    Review
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recently processed by Treasurer -->
    <div class="card">
        <h2>Recently Processed by Treasurer</h2>
        <div class="table-wrapper">
            <table class="proposals-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Event Date</th>
                        <th>Budget</th>
                        <th>Treasurer Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$recentRes || mysqli_num_rows($recentRes) === 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;color:#6b7280;">
                            No recent actions yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($r = mysqli_fetch_assoc($recentRes)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['title']); ?></td>
                            <td><?php echo htmlspecialchars($r['event_date']); ?></td>
                            <td>₱<?php echo number_format($r['proposed_budget'], 2); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($r['treasurer_status'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
