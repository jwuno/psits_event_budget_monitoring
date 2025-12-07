<?php
require_once '../../includes/auth.php';
requireRole('student');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

$proposalId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($proposalId <= 0) {
    echo "<p style='max-width:1200px;margin:2rem auto;color:#b91c1c;'>Invalid proposal ID.</p>";
    include '../../includes/footer.php';
    exit;
}

// Fetch proposal (read-only for students)
// You can restrict to approved only if you want by adding: AND p.status = 'approved'
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
    echo "<p style='max-width:1200px;margin:2rem auto;color:#b91c1c;'>Proposal not found.</p>";
    include '../../includes/footer.php';
    exit;
}

$proposal = mysqli_fetch_assoc($res);

// status pill helper
$status = strtolower($proposal['status']);
switch ($status) {
    case 'approved':
        $statusClass = 'status-pill--approved';
        break;
    case 'pending':
        $statusClass = 'status-pill--pending';
        break;
    case 'rejected':
        $statusClass = 'status-pill--rejected';
        break;
    case 'returned':
        $statusClass = 'status-pill--returned';
        break;
    default:
        $statusClass = 'status-pill--default';
}
?>
<div class="dashboard">
    <div class="dashboard-header" style="margin-bottom:1rem;">
        <h1>Event Details</h1>
        <p>Read-only view of a PSITS proposal for transparency.</p>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
            <div>
                <h2 style="margin-bottom:0.25rem;"><?php echo htmlspecialchars($proposal['title']); ?></h2>
                <p style="margin:0;color:#6b7280;font-size:0.9rem;">
                    Prepared by:
                    <strong><?php echo htmlspecialchars($proposal['prepared_by'] ?? $proposal['created_by']); ?></strong>
                </p>
                <p style="margin:0.15rem 0 0;color:#6b7280;font-size:0.9rem;">
                    Date Submitted:
                    <?php echo htmlspecialchars($proposal['date_submitted']); ?>
                </p>
            </div>
            <div style="text-align:right;">
                <span class="status-pill <?php echo $statusClass; ?>">
                    <?php echo strtoupper(htmlspecialchars($proposal['status'])); ?>
                </span>
                <p style="margin-top:0.4rem;color:#6b7280;font-size:0.8rem;">
                    Current Stage: <strong><?php echo ucfirst(htmlspecialchars($proposal['current_stage'])); ?></strong>
                </p>
            </div>
        </div>

        <hr style="margin:1rem 0;border:none;border-top:1px solid #e5e7eb;">

        <div class="form-grid" style="row-gap:0.9rem;">
            <div class="form-group">
                <label>Event Date</label>
                <div>
                    <?php echo htmlspecialchars($proposal['event_date']); ?>
                </div>
            </div>

            <div class="form-group">
                <label>Venue</label>
                <div>
                    <?php echo htmlspecialchars($proposal['venue']); ?>
                </div>
            </div>

            <?php if (!empty($proposal['expected_participants'])): ?>
                <div class="form-group">
                    <label>Expected Participants</label>
                    <div>
                        <?php echo (int) $proposal['expected_participants']; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Proposed Budget</label>
                <div>
                    ₱<?php echo number_format((float)$proposal['proposed_budget'], 2); ?>
                </div>
            </div>
        </div>

        <?php if (!empty($proposal['description'])): ?>
            <div class="form-group" style="margin-top:1rem;">
                <label>Event Description / Rationale</label>
                <div style="font-size:0.9rem;color:#111827;white-space:pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($proposal['description'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($proposal['budget_breakdown'])): ?>
            <div class="form-group" style="margin-top:1rem;">
                <label>Budget Breakdown</label>
                <div style="font-size:0.9rem;color:#111827;white-space:pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($proposal['budget_breakdown'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top:1.25rem;display:flex;justify-content:flex-end;">
            <a href="dashboard.php" class="btn btn-sm">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
