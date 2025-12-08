<?php
require_once '../../includes/auth.php';
requireRole('treasurer');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

/* ---------------------------
   STATUS PILL HELPER (local)
   --------------------------- */

function renderTreasurerStatusPill($statusRaw) {
    $status = strtolower(trim((string)$statusRaw));
    $class  = 'status-pill status-pill--default';
    $label  = ucfirst($status);

    switch ($status) {
        case 'pending':
            $class = 'status-pill status-pill--pending';
            $label = 'Pending';
            break;
        case 'approved':
            $class = 'status-pill status-pill--approved';
            $label = 'Approved';
            break;
        case 'rejected':
            $class = 'status-pill status-pill--rejected';
            $label = 'Rejected';
            break;
        case 'returned':
            $class = 'status-pill status-pill--returned';
            $label = 'Returned';
            break;
        default:
            if ($label === '' || $label === ' ') {
                $label = 'N/A';
            }
            break;
    }

    return '<span class="' . $class . '">' .
           htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
           '</span>';
}

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
    SELECT id,
           title,
           event_date,
           proposed_budget,
           president_status,
           adviser_status
    FROM proposals
    WHERE treasurer_status IN ('approved', 'returned')
    ORDER BY review_date DESC
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

        <div class="stat-card budget">
            <span class="stat-label">Total Final Approved Budget</span>
            <span class="stat-value">₱<?php echo number_format($totalFinalBudget, 2); ?></span>
        </div>
    </div>

    <!-- Proposals awaiting Treasurer review (includes returned from President) -->
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
                            <td>
                                <?php echo renderTreasurerStatusPill($p['status']); ?>
                            </td>
                            <td>
                                <?php
                                // Who sent it here?
                                echo $p['returned_from']
                                     ? 'Returned by ' . ucfirst(htmlspecialchars($p['returned_from']))
                                     : 'Secretary';
                                ?>
                            </td>
                            <td class="actions-cell">
                                <a href="review_proposal.php?id=<?php echo (int)$p['id']; ?>"
                                   class="btn btn-sm btn-primary">
                                    Review
                                </a>
                                <!-- Indicator: new / returned item awaiting Treasurer -->
                                <span class="attention-flag"
                                      title="Awaiting your review">!</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:0.5rem;font-size:0.8rem;color:#6b7280;">
            <span class="attention-flag"
                  style="vertical-align:middle;margin-right:0.25rem;">!</span>
            marks proposals that are <strong>currently waiting for your review</strong>
            (new or returned).
        </p>
    </div>

    <!-- Recently processed by Treasurer -->
    <div class="card">
        <h2>Recently Processed by Treasurer</h2>
        <div class="table-wrapper table-scroll">
            <table class="proposals-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Event Date</th>
                        <th>Budget</th>
                        <th>President Status</th>
                        <th>Adviser Status</th>
                        <th style="width:120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$recentRes || mysqli_num_rows($recentRes) === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#6b7280;">
                            No recent actions yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($r = mysqli_fetch_assoc($recentRes)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['title']); ?></td>
                            <td><?php echo htmlspecialchars($r['event_date']); ?></td>
                            <td>₱<?php echo number_format($r['proposed_budget'], 2); ?></td>
                            <td><?php echo renderTreasurerStatusPill($r['president_status']); ?></td>
                            <td><?php echo renderTreasurerStatusPill($r['adviser_status']); ?></td>
                            <td class="actions-cell">
                                <a href="view_proposal.php?id=<?php echo (int)$r['id']; ?>"
                                   class="btn btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
