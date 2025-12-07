<?php
require_once '../../includes/auth.php';
requireRole('secretary');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

// Secretary is identified by username/email stored in created_by
$currentUser = mysqli_real_escape_string($conn, $_SESSION['username'] ?? '');

/* ---------- STATS: my proposals by status ---------- */
$secCounts = [
    'pending'  => 0,
    'returned' => 0,
    'approved' => 0,
    'rejected' => 0
];

$sqlSec = "
    SELECT status, COUNT(*) AS cnt
    FROM proposals
    WHERE created_by = '$currentUser'
    GROUP BY status
";
if ($res = mysqli_query($conn, $sqlSec)) {
    while ($row = mysqli_fetch_assoc($res)) {
        $key = strtolower($row['status']);
        if (isset($secCounts[$key])) {
            $secCounts[$key] = (int)$row['cnt'];
        }
    }
}

/* ---------- MY PROPOSALS LIST ---------- */
$sqlList = "
    SELECT id, title, event_date, venue,
           proposed_budget, status, current_stage
    FROM proposals
    WHERE created_by = '$currentUser'
    ORDER BY date_submitted DESC
";
$myProposals = mysqli_query($conn, $sqlList);

/* ---------- STATUS PILL HELPER ---------- */
function renderStatusPill($statusRaw) {
    $status = strtolower((string)$statusRaw);
    $class  = 'status-pill status-pill--default';
    $label  = ucfirst($status);

    switch ($status) {
        case 'pending':
            $class = 'status-pill status-pill--pending';
            $label = 'Pending';
            break;
        case 'returned':
            $class = 'status-pill status-pill--returned';
            $label = 'Returned for Revision';
            break;
        case 'approved':
            $class = 'status-pill status-pill--approved';
            $label = 'Final Approved';
            break;
        case 'rejected':
            $class = 'status-pill status-pill--rejected';
            $label = 'Final Rejected';
            break;
    }

    return '<span class="' . $class . '">' .
           htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
           '</span>';
}
?>
<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
        <div>
            <h1>Secretary Dashboard</h1>
            <p>Create and track your event proposals.</p>
        </div>
        <a href="create_proposal.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Create Proposal
        </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card pending">
            <span class="stat-label">Pending Proposals</span>
            <span class="stat-value"><?php echo $secCounts['pending']; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Returned for Revision</span>
            <span class="stat-value"><?php echo $secCounts['returned']; ?></span>
        </div>
        <div class="stat-card approved">
            <span class="stat-label">Approved Proposals</span>
            <span class="stat-value"><?php echo $secCounts['approved']; ?></span>
        </div>
        <div class="stat-card rejected">
            <span class="stat-label">Rejected Proposals</span>
            <span class="stat-value"><?php echo $secCounts['rejected']; ?></span>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <h2>My Proposals</h2>
        <div class="table-wrapper table-scroll">
            <table class="proposals-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Stage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($myProposals && mysqli_num_rows($myProposals) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($myProposals)): ?>
                        <?php
                            // show attention flag only if returned & back to secretary
                            $needsAttention = (
                                $p['status'] === 'returned' &&
                                $p['current_stage'] === 'secretary'
                            );
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo htmlspecialchars($p['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($p['venue']); ?></td>
                            <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>

                            <!-- colored status pill -->
                            <td>
                                <?php echo renderStatusPill($p['status']); ?>
                            </td>

                            <td><?php echo ucfirst(htmlspecialchars($p['current_stage'])); ?></td>
                            <td class="actions-cell">
                                <a href="view_proposal.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm">
                                    View
                                </a>
                                <?php if (in_array($p['status'], ['pending', 'returned'], true)): ?>
                                    <a href="edit_proposal.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-primary">
                                        Edit
                                    </a>
                                    <?php if ($needsAttention): ?>
                                        <span class="attention-flag" title="Returned for revision">!</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            No proposals yet. Click “Create Proposal” to submit one.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p style="margin-top:0.5rem;font-size:0.8rem;color:#6b7280;">
            <span class="attention-flag" style="vertical-align:middle;margin-right:0.25rem;">!</span>
            indicates a proposal that was <strong>returned to you for revision</strong>.
        </p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
