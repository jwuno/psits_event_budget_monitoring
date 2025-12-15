<?php
require_once '../../includes/auth.php';
requireRole('pio');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

/* ---------- Status pill helper (shared style) ---------- */
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

        return '<span class="' . $class . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

/* ---------- Get proposal ID ---------- */
$proposalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($proposalId <= 0) {
    ?>
    <div class="dashboard">
        <div class="card">
            <h2>Event not found</h2>
            <p style="color:#6b7280;">The requested event is missing or the link is invalid.</p>
            <a href="dashboard.php" class="btn btn-sm" style="margin-top:0.75rem;">← Back to Dashboard</a>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

/* ---------- Fetch proposal + prepared_by ---------- */
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
            <h2>Event not found</h2>
            <p style="color:#6b7280;">The requested event could not be retrieved.</p>
            <a href="dashboard.php" class="btn btn-sm" style="margin-top:0.75rem;">← Back to Dashboard</a>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

/* ---------- Convenience vars ---------- */
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

/* Attachment path guess */
$attachment = '';
if (!empty($proposal['attachment_path'])) {
    $attachment = $proposal['attachment_path'];
} elseif (!empty($proposal['attachment'])) {
    $attachment = $proposal['attachment'];
}
$attachmentUrl = $attachment !== '' ? '../../uploads/' . rawurlencode(basename($attachment)) : '';


/* ---------- Handle inline announcement creation ---------- */
$annSuccess = '';
$annError   = '';

// Only allow announcements for fully approved events
$canCreateAnnouncement = (strtolower($status) === 'approved');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement'])) {
    $annTitle   = trim($_POST['ann_title'] ?? '');
    $annContent = trim($_POST['ann_content'] ?? '');

    if (!$canCreateAnnouncement) {
        $annError = 'Announcements can only be created for fully approved events.';
    } elseif ($annTitle === '' || $annContent === '') {
        $annError = 'Please provide both an announcement title and content.';
    } else {
        $titleEsc   = mysqli_real_escape_string($conn, $annTitle);
        $contentEsc = mysqli_real_escape_string($conn, $annContent);
        $createdBy  = mysqli_real_escape_string($conn, $_SESSION['username'] ?? 'PIO');
        $pid        = (int)$proposalId;

        $insertSql = "
            INSERT INTO announcements (proposal_id, title, content, created_by, created_at)
            VALUES ($pid, '$titleEsc', '$contentEsc', '$createdBy', NOW())
        ";
        if (mysqli_query($conn, $insertSql)) {
            $annSuccess = 'Announcement has been posted. Students can now see it under Latest Announcements.';
            // clear textarea after success
            $_POST['ann_content'] = '';
            // Redirect back to dashboard after posting announcement
            header("Location: dashboard.php");
            exit;
        } else {
            $annError = 'Failed to save announcement. Please try again.';
        }
    }
}

/* Default values for the form fields */
$defaultAnnTitle   = $_POST['ann_title']   ?? '';
$defaultAnnContent = $_POST['ann_content'] ?? '';
?>
<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div>
            <h1>View Approved Event (PIO)</h1>
            <p>Use these details when preparing posters, captions, and announcements.</p>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:flex-end;">
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
        <p style="white-space:pre-wrap;font-size:0.9rem;color:#111827;margin-top:0.5rem; max-height:220px;overflow-y:auto;padding-right:4px;">
            <?php echo $description !== '' ? htmlspecialchars($description) : 'No description provided.'; ?>
        </p>
    </div>

    <!-- Budget Breakdown -->
    <div class="card">
        <h2>Budget Breakdown</h2>
        <p style="white-space:pre-wrap;font-size:0.9rem;color:#111827;margin-top:0.5rem; max-height:220px;overflow-y:auto;padding-right:4px;">
            <?php echo $breakdown !== '' ? htmlspecialchars($breakdown) : 'No detailed budget breakdown provided.'; ?>
        </p>
    </div>

    <!-- Attachment -->
    <div class="card">
        <h2>Attachment</h2>
        <?php if ($attachmentUrl): ?>
            <p style="font-size:0.9rem;margin-top:0.4rem;">
                <a href="<?php echo htmlspecialchars($attachmentUrl); ?>" target="_blank" class="btn btn-sm btn-primary">
                    View / Download Attachment
                </a>
            </p>
        <?php else: ?>
            <p style="color:#6b7280;font-size:0.9rem;margin-top:0.4rem;">
                No attachment was uploaded for this proposal.
            </p>
        <?php endif; ?>
    </div>

    <!-- Create Announcement (inline for PIO) -->
    <div class="card">
        <h2>Create Announcement for Students</h2>

        <?php if ($annSuccess): ?>
            <p style="margin-top:0.4rem;font-size:0.85rem;color:#166534;background:#ecfdf3;border:1px solid #bbf7d0;padding:0.45rem 0.6rem;border-radius:8px;">
                <?php echo htmlspecialchars($annSuccess); ?>
            </p>
        <?php endif; ?>

        <?php if ($annError): ?>
            <p style="margin-top:0.4rem;font-size:0.85rem;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;padding:0.45rem 0.6rem;border-radius:8px;">
                <?php echo htmlspecialchars($annError); ?>
            </p>
        <?php endif; ?>

        <?php if (!$canCreateAnnouncement): ?>
            <p style="margin-top:0.5rem;color:#6b7280;font-size:0.9rem;">
                This event is not yet fully approved. Announcements can only be posted for
                proposals with an overall status of <strong>Approved</strong>.
            </p>
        <?php else: ?>
            <form method="post" style="margin-top:0.75rem;">
                <div class="form-group" style="max-width:600px;">
                    <label for="ann_title">Announcement Title</label>
                    <input
                        type="text"
                        id="ann_title"
                        name="ann_title"
                        required
                        value="<?php echo htmlspecialchars($defaultAnnTitle, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="form-group" style="max-width:800px;">
                    <label for="ann_content">Announcement Content</label>
                    <textarea
                        id="ann_content"
                        name="ann_content"
                        rows="4"
                        required
                    ><?php echo htmlspecialchars($defaultAnnContent, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <small style="color:#6b7280;font-size:0.8rem;">
                        This text will appear on the Student Transparency Dashboard under <strong>Latest Announcements</strong>.
                    </small>
                </div>

                <button type="submit" name="create_announcement" class="btn btn-primary">
                    Post Announcement
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
