<?php
require_once '../../includes/auth.php';
requireRole('student');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

if (!function_exists('renderStatusPill')) {
    function renderStatusPill($statusRaw) {
        $status = strtolower(trim((string)$statusRaw));
        $class  = 'status-pill status-pill--default';
        $label  = $status === '' ? 'N/A' : ucfirst($status);

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
        }

        return '<span class="' . $class . '">' .
               htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
               '</span>';
    }
}

/* ----- Get proposal ID ----- */
$proposalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($proposalId <= 0) {
    ?>
    <div class="dashboard">
        <div class="card">
            <h2>Proposal not found</h2>
            <p style="color:#6b7280;">The requested proposal is missing or the link is invalid.</p>
            <a href="dashboard.php" class="btn btn-sm" style="margin-top:0.75rem;">← Back to Dashboard</a>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

/* ----- Fetch proposal + prepared_by ----- */
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
$proposal = ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : null;

if (!$proposal) {
    ?>
    <div class="dashboard">
        <div class="card">
            <h2>Proposal not found</h2>
            <p style="color:#6b7280;">The requested proposal could not be retrieved.</p>
            <a href="dashboard.php" class="btn btn-sm" style="margin-top:0.75rem;">← Back to Dashboard</a>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

/* ----- Convenience variables ----- */
$title          = $proposal['title'] ?? '';
$preparedBy     = $proposal['prepared_by'] ?: ($proposal['created_by'] ?? '');
$venue          = $proposal['venue'] ?? '';
$expected       = $proposal['expected_participants'] ?? '';
$budget         = (float)($proposal['proposed_budget'] ?? 0);
$description    = trim((string)($proposal['description'] ?? ''));
$breakdown      = trim((string)($proposal['budget_breakdown'] ?? ''));
$status         = $proposal['status'] ?? '';
$currentStage   = $proposal['current_stage'] ?? '';
$treasStatus    = $proposal['treasurer_status'] ?? '';
$presStatus     = $proposal['president_status'] ?? '';
$advStatus      = $proposal['adviser_status'] ?? '';
$dateSubmitted  = $proposal['date_submitted'] ?? '';

$eventStart = $proposal['event_start_date'] ?? ($proposal['event_date'] ?? '');
$eventEnd   = $proposal['event_end_date'] ?? $eventStart;
?>
<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div>
            <h1>View Approved Proposal</h1>
            <p>Transparency view of the event details and its approved budget.</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-sm">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Main event + status card -->
    <div class="card">
        <h2 style="margin-bottom:0.35rem;"><?php echo htmlspecialchars($title); ?></h2>
        <p style="color:#6b7280;font-size:0.9rem;margin:0;">
            Reference ID: <strong>#<?php echo (int)$proposalId; ?></strong>
            <?php if (!empty($dateSubmitted)): ?>
                · Submitted on <?php echo htmlspecialchars($dateSubmitted); ?>
            <?php endif; ?>
        </p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;margin-top:1.25rem;">
            <!-- Event Summary -->
            <div style="border-radius:12px;border:1px solid #e5e7eb;padding:0.9rem 1rem;">
                <h3 style="margin:0 0 0.6rem;font-size:0.95rem;color:#111827;">Event Summary</h3>

                <div style="font-size:0.88rem;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.35rem;">
                        <span style="color:#6b7280;">Prepared by</span>
                        <span style="font-weight:500;"><?php echo htmlspecialchars($preparedBy); ?></span>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-bottom:0.35rem;">
                        <span style="color:#6b7280;">Venue</span>
                        <span style="font-weight:500;"><?php echo htmlspecialchars($venue ?: 'Not specified'); ?></span>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-bottom:0.35rem;">
                        <span style="color:#6b7280;">Event Start</span>
                        <span style="font-weight:500;"><?php echo htmlspecialchars($eventStart ?: 'Not set'); ?></span>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-bottom:0.35rem;">
                        <span style="color:#6b7280;">Event End</span>
                        <span style="font-weight:500;"><?php echo htmlspecialchars($eventEnd ?: 'Not set'); ?></span>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-bottom:0.35rem;">
                        <span style="color:#6b7280;">Expected Participants</span>
                        <span style="font-weight:500;">
                            <?php echo $expected !== '' ? (int)$expected : 'Not indicated'; ?>
                        </span>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:0.4rem;">
                        <span style="color:#6b7280;">Approved Budget</span>
                        <span style="font-weight:700;">₱<?php echo number_format($budget, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Approval Status (read-only) -->
            <div style="border-radius:12px;border:1px solid #e5e7eb;padding:0.9rem 1rem;">
                <h3 style="margin:0 0 0.6rem;font-size:0.95rem;color:#111827;">Approval Status</h3>

                <div style="font-size:0.88rem;display:flex;flex-direction:column;gap:0.4rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#6b7280;">Overall Status</span>
                        <span><?php echo renderStatusPill($status); ?></span>
                    </div>

                    <hr style="border:none;border-top:1px dashed #e5e7eb;margin:0.4rem 0;">

                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#6b7280;">Treasurer</span>
                        <span><?php echo renderStatusPill($treasStatus); ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#6b7280;">President</span>
                        <span><?php echo renderStatusPill($presStatus); ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#6b7280;">Adviser</span>
                        <span><?php echo renderStatusPill($advStatus); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="card">
        <h2>Event Description / Rationale</h2>
        <p style="white-space:pre-wrap;font-size:0.9rem;color:#111827;margin-top:0.5rem;">
            <?php echo $description !== '' ? htmlspecialchars($description) : 'No description provided.'; ?>
        </p>
    </div>

    <!-- Budget Breakdown -->
    <div class="card">
        <h2>Budget Breakdown</h2>
        <p style="white-space:pre-wrap;font-size:0.9rem;color:#111827;margin-top:0.5rem;">
            <?php echo $breakdown !== '' ? htmlspecialchars($breakdown) : 'No detailed budget breakdown provided.'; ?>
        </p>
    </div>

    <!-- No attachment card for students (per your request) -->
</div>

<?php include '../../includes/footer.php'; ?>
