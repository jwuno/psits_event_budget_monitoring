<?php
require_once '../../includes/auth.php';
requireRole('secretary');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: my_proposals.php');
    exit;
}

// Fetch proposal (make sure it belongs to this secretary)
$username = mysqli_real_escape_string($conn, $_SESSION['username']);

$sql = "
    SELECT *
    FROM proposals
    WHERE id = $id
      AND created_by = '$username'
";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) === 0) {
    ?>
    <div class="dashboard">
        <div class="card">
            <h2>Proposal Not Found</h2>
            <p>You may not have permission to edit this proposal.</p>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='my_proposals.php';">
                <i class="fa-solid fa-arrow-left"></i> Back to My Proposals
            </button>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

$proposal = mysqli_fetch_assoc($res);

/**
 * Secretary can edit ONLY when the proposal is back at Secretary stage,
 * typically returned by the Treasurer.
 */
$canEdit = ($proposal['current_stage'] === 'secretary');

if (!$canEdit) {
    ?>
    <div class="dashboard">
        <div class="card">
            <h2>Editing Locked</h2>
            <p>
                This proposal is currently under review and cannot be edited.
                You can only edit proposals that have been returned to you.
            </p>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='my_proposals.php';">
                <i class="fa-solid fa-arrow-left"></i> Back to My Proposals
            </button>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

/* -----------------------------
   Handle POST: update + resubmit
   ----------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $title        = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description  = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $event_date   = mysqli_real_escape_string($conn, $_POST['event_date'] ?? '');
    $venue        = mysqli_real_escape_string($conn, $_POST['venue'] ?? '');
    $participants = (int)($_POST['expected_participants'] ?? 0);
    $budget       = (float)($_POST['proposed_budget'] ?? 0);
    $breakdownRaw = $_POST['budget_breakdown'] ?? '';
    $breakdown    = mysqli_real_escape_string($conn, $breakdownRaw);

    // Attachment: keep old unless new file uploaded
    $attachmentPath = $proposal['attachment_path'];

    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir  = '../../uploads/';
        $fileName   = time() . '_' . basename($_FILES['attachment']['name']);
        $targetPath = $uploadDir . $fileName;

        // Make sure uploads directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
            // Save path relative to project root (so links work in browser)
            $attachmentPath = 'uploads/' . $fileName;
        } else {
            $_SESSION['error'] = 'Failed to upload attachment. Please try again.';
        }
    }

    // When Secretary edits & resubmits, send back to Treasurer
    $status          = 'pending';
    $current_stage   = 'treasurer';
    $treasurerStatus = 'pending';

    $update = "
        UPDATE proposals
        SET
            title                 = '$title',
            description           = '$description',
            event_date            = '$event_date',
            venue                 = '$venue',
            expected_participants = $participants,
            proposed_budget       = $budget,
            budget_breakdown      = '$breakdown',
            attachment_path       = " . ($attachmentPath ? "'" . mysqli_real_escape_string($conn, $attachmentPath) . "'" : "NULL") . ",
            status                = '$status',
            current_stage         = '$current_stage',
            treasurer_status      = '$treasurerStatus',
            returned_from         = NULL,
            date_submitted        = NOW()
        WHERE id = $id
          AND created_by = '$username'
    ";

    if (mysqli_query($conn, $update)) {
        $_SESSION['success'] = 'Proposal updated and resubmitted to the Treasurer.';
        header('Location: my_proposals.php');
        exit;
    } else {
        $_SESSION['error'] = 'Error updating proposal: ' . mysqli_error($conn);
        // reload latest data if error
        $res = mysqli_query($conn, $sql);
        $proposal = mysqli_fetch_assoc($res);
    }
}
?>

<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
        <div>
            <h1>Edit & Resubmit Proposal</h1>
            <p>Adjust your event details based on Treasurer feedback, then resubmit.</p>
        </div>

        <button type="button" class="btn btn-sm" onclick="window.location.href='my_proposals.php';">
            <i class="fa-solid fa-arrow-left"></i> Back to My Proposals
        </button>
    </div>

    <div class="card">
        <h2>Proposal Details</h2>

        <form action="edit_proposal.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title"
                       value="<?php echo htmlspecialchars($proposal['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Event Description</label>
                <textarea id="description" name="description" rows="4" required><?php
                    echo htmlspecialchars($proposal['description']);
                ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date"
                           value="<?php echo htmlspecialchars($proposal['event_date']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="venue">Venue</label>
                    <input type="text" id="venue" name="venue"
                           value="<?php echo htmlspecialchars($proposal['venue']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="expected_participants">Expected Participants</label>
                    <input type="number" id="expected_participants" name="expected_participants" min="1"
                           value="<?php echo (int)$proposal['expected_participants']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="proposed_budget">Proposed Budget (₱)</label>
                    <input type="number" step="0.01" id="proposed_budget" name="proposed_budget"
                           value="<?php echo htmlspecialchars($proposal['proposed_budget']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="budget_breakdown">Budget Breakdown</label>
                <textarea id="budget_breakdown" name="budget_breakdown" rows="4" required><?php
                    echo htmlspecialchars($proposal['budget_breakdown']);
                ?></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Attachment (optional)</label>
                <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png">
                <?php if (!empty($proposal['attachment_path'])): ?>
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:#6b7280;">
                        Current file:
                        <a href="<?php echo '../../' . htmlspecialchars($proposal['attachment_path']); ?>" target="_blank">
                            View existing attachment
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Show Treasurer remarks for context -->
            <div class="form-group">
                <label>Treasurer Remarks</label>
                <p style="white-space:pre-wrap;margin-top:0.25rem;">
                    <?php
                    echo ($proposal['treasurer_remarks'] !== null && $proposal['treasurer_remarks'] !== '')
                        ? htmlspecialchars($proposal['treasurer_remarks'])
                        : 'None.';
                    ?>
                </p>
            </div>

            <div style="margin-top:1rem;display:flex;justify-content:flex-end;gap:0.5rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Save Changes &amp; Resubmit
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
