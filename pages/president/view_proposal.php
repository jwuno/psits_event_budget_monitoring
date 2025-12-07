<?php
// pages/president/view_proposal.php

require_once '../../includes/auth.php';
requireRole('president');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo "<p>Invalid proposal ID.</p>";
    include '../../includes/footer.php';
    exit;
}
$proposalId = (int) $_GET['id'];

$sql = "
    SELECT p.*,
           u.full_name AS prepared_by
    FROM proposals p
    LEFT JOIN users u
        ON p.created_by = u.username
    WHERE p.id = $proposalId
    LIMIT 1
";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) === 0) {
    echo "<p>Proposal not found.</p>";
    include '../../includes/footer.php';
    exit;
}

$proposal = mysqli_fetch_assoc($res);

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function formatStatusBadge($status) {
    $status = strtolower((string)$status);
    $class = 'status-badge status-pending';
    $label = ucfirst($status);

    if ($status === 'approved') {
        $class = 'status-badge status-approved';
    } elseif ($status === 'rejected') {
        $class = 'status-badge status-rejected';
    } elseif ($status === 'returned') {
        $class = 'status-badge status-returned';
    }

    if ($status === '') {
        $label = 'N/A';
    }

    return '<span class="' . $class . '">' . $label . '</span>';
}

function formatEventSchedule($proposal) {
    $start  = $proposal['event_start_date'] ?? '';
    $end    = $proposal['event_end_date'] ?? '';
    $single = $proposal['event_date'] ?? '';

    if (empty($start) && !empty($single)) {
        return date('F j, Y', strtotime($single));
    }

    if (empty($start)) {
        return 'Not set';
    }

    if (empty($end) || $end === $start) {
        return date('F j, Y', strtotime($start));
    }

    $startTs = strtotime($start);
    $endTs   = strtotime($end);

    if ($startTs === false || $endTs === false) {
        return e($start) . ' - ' . e($end);
    }

    if (date('Y', $startTs) === date('Y', $endTs)) {
        if (date('m', $startTs) === date('m', $endTs)) {
            return date('F j', $startTs) . '–' . date('j, Y', $endTs);
        }
        return date('F j', $startTs) . ' – ' . date('F j, Y', $endTs);
    }

    return date('F j, Y', $startTs) . ' – ' . date('F j, Y', $endTs);
}

$eventSchedule = formatEventSchedule($proposal);
?>

<div class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>View Proposal</h1>
            <p>Review the full details of this event and its approval status.</p>
        </div>

        <div class="page-actions">
            <a href="dashboard.php" class="btn btn-outline btn-sm">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><?php echo e($proposal['title']); ?></h2>
            <div style="font-size:0.85rem;color:#6b7280;">
                Prepared by
                <strong><?php echo e($proposal['prepared_by'] ?: $proposal['created_by']); ?></strong>
                · Submitted
                <?php echo e(!empty($proposal['date_submitted']) ? $proposal['date_submitted'] : 'N/A'); ?>
            </div>
        </div>

        <div class="card-body">
            <div class="table-wrapper">
                <table class="proposals-table">
                    <tbody>
                        <tr>
                            <th style="width:220px;">Event Schedule</th>
                            <td><?php echo e($eventSchedule); ?></td>
                        </tr>
                        <tr>
                            <th>Venue</th>
                            <td><?php echo e($proposal['venue']); ?></td>
                        </tr>
                        <tr>
                            <th>Target Participants</th>
                            <td>
                                <?php
                                    $exp = $proposal['expected_participants'] ?? null;
                                    echo $exp !== null && $exp !== '' ? e($exp) : 'N/A';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Proposed Budget</th>
                            <td>₱<?php echo number_format((float)($proposal['proposed_budget'] ?? 0), 2); ?></td>
                        </tr>
                        <tr>
                            <th>Overall Status</th>
                            <td><?php echo formatStatusBadge($proposal['status']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($proposal['description'])): ?>
        <div class="card">
            <div class="card-header">
                <h2>Event Description / Rationale</h2>
            </div>
            <div class="card-body">
                <p style="font-size:0.9rem;white-space:pre-wrap;">
                    <?php echo e($proposal['description']); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Approval Status &amp; Remarks</h2>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Stage</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Treasurer</td>
                            <td><?php echo formatStatusBadge($proposal['treasurer_status'] ?? ''); ?></td>
                            <td><?php echo e($proposal['treasurer_remarks'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <td>President</td>
                            <td><?php echo formatStatusBadge($proposal['president_status'] ?? ''); ?></td>
                            <td><?php echo e($proposal['president_remarks'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <td>Adviser</td>
                            <td><?php echo formatStatusBadge($proposal['adviser_status'] ?? ''); ?></td>
                            <td><?php echo e($proposal['adviser_remarks'] ?? ''); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
