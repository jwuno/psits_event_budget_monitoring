<?php
// pages/adviser/view_proposal.php

require_once '../../includes/auth.php';
requireRole('adviser');

require_once '../../db_connect.php';
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
?>
<div class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>View Proposal</h1>
            <p>Final review of the event proposal before approval or rejection.</p>
        </div>
        <div class="page-actions">
            <a href="pending_proposals.php" class="btn btn-outline btn-sm">
                ← Back to Pending Proposals
            </a>
            <a href="../shared/print_proposal.php?id=<?php echo $proposal['id']; ?>"
               target="_blank"
               class="btn btn-outline btn-sm">
                <i class="fa-solid fa-print"></i>
                Print Proposal
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
                            <th style="width:200px;">Event Date</th>
                            <td><?php echo e($proposal['event_date']); ?></td>
                        </tr>
                        <tr>
                            <th>Venue</th>
                            <td><?php echo e($proposal['venue']); ?></td>
                        </tr>
                        <tr>
                            <th>Target Participants</th>
                            <td><?php echo e($proposal['participants'] ?? 'N/A'); ?></td>
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

    <div class="grid-2">
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

        <?php if (!empty($proposal['objectives'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Objectives</h2>
                </div>
                <div class="card-body">
                    <p style="font-size:0.9rem;white-space:pre-wrap;">
                        <?php echo e($proposal['objectives']); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>

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
