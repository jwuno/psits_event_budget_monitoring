<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

$proposal_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($proposal_id <= 0) {
    $_SESSION['error'] = "No proposal specified.";
    header("Location: my_proposals.php");
    exit;
}

// RESUBMIT HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resubmit') {

    $title                 = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date            = mysqli_real_escape_string($conn, $_POST['event_date']);
    $venue                 = mysqli_real_escape_string($conn, $_POST['venue']);
    $expected_participants = (int)$_POST['expected_participants'];
    $proposed_budget       = (float)$_POST['proposed_budget'];
    $description           = mysqli_real_escape_string($conn, $_POST['description']);
    $objectives            = mysqli_real_escape_string($conn, $_POST['objectives']);
    $activities            = mysqli_real_escape_string($conn, $_POST['activities']);
    $expected_outcomes     = mysqli_real_escape_string($conn, $_POST['expected_outcomes']);
    $budget_breakdown      = mysqli_real_escape_string($conn, $_POST['budget_breakdown']);

    $update_sql = "
        UPDATE proposals SET
            title                 = '$title',
            event_date            = '$event_date',
            venue                 = '$venue',
            expected_participants = $expected_participants,
            proposed_budget       = $proposed_budget,
            description           = '$description',
            objectives            = '$objectives',
            activities            = '$activities',
            expected_outcomes     = '$expected_outcomes',
            budget_breakdown      = '$budget_breakdown',
            status                = 'pending',
            current_stage         = 'treasurer',
            treasurer_status      = 'pending',
            returned_from         = NULL
        WHERE id = $proposal_id
    ";

    if (mysqli_query($conn, $update_sql)) {
        $res    = mysqli_query($conn, "SELECT title FROM proposals WHERE id = $proposal_id");
        $row    = mysqli_fetch_assoc($res);
        $ptitle = $row['title'] ?? 'an event';

        $by  = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Secretary';
        $msg = "$by updated and resubmitted the proposal \"$ptitle\" for budget review.";
        addNotification($conn, 'treasurer', $msg, $by);

        $_SESSION['success'] = "Proposal updated and resubmitted to Treasurer.";
        header("Location: pending_proposals.php");
        exit;
    } else {
        $_SESSION['error'] = "Error resubmitting proposal: " . mysqli_error($conn);
        header("Location: view_proposal.php?id=" . $proposal_id);
        exit;
    }
}

// FETCH PROPOSAL
$sql = "SELECT * FROM proposals WHERE id = $proposal_id";
$res = mysqli_query($conn, $sql);
$proposal = mysqli_fetch_assoc($res);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found.";
    header("Location: my_proposals.php");
    exit;
}

$can_edit = ($proposal['status'] === 'returned' && $proposal['current_stage'] === 'secretary');

include '../../includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>View Proposal</h1>
            <p>Details of your submitted event proposal.</p>
        </div>
        <div class="header-actions">
            <a href="my_proposals.php" class="btn-back">← Back to My Proposals</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="proposal-detail">
        <div class="proposal-card">
            <div class="proposal-header">
                <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                <span class="status-badge status-<?php echo strtolower($proposal['status']); ?>">
                    <?php echo ucfirst($proposal['status']); ?>
                    (<?php echo ucfirst($proposal['current_stage']); ?>)
                </span>
            </div>

            <div class="proposal-details">
                <div class="detail-item">
                    <strong>Event Date:</strong>
                    <span><?php echo date('M j, Y', strtotime($proposal['event_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Venue:</strong>
                    <span><?php echo htmlspecialchars($proposal['venue']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Participants:</strong>
                    <span><?php echo (int)$proposal['expected_participants']; ?> students</span>
                </div>
                <div class="detail-item">
                    <strong>Proposed Budget:</strong>
                    <span>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                </div>
            </div>

            <?php if (!empty($proposal['treasurer_remarks'])): ?>
                <div class="detail-section">
                    <h3>Treasurer Remarks</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['treasurer_remarks'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['president_remarks'])): ?>
                <div class="detail-section">
                    <h3>President Remarks</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['president_remarks'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['adviser_remarks'])): ?>
                <div class="detail-section">
                    <h3>Adviser Remarks</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['adviser_remarks'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['rejection_reason'])): ?>
                <div class="detail-section">
                    <h3>Latest Return / Rejection Reason</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['rejection_reason'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['attachment_path'])): ?>
                <div class="detail-section">
                    <h3>Attachment</h3>
                    <a href="../../uploads/<?php echo htmlspecialchars($proposal['attachment_path']); ?>" 
                       target="_blank" class="btn-download">
                        View Attachment
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($can_edit): ?>
            <div class="action-card">
                <h3>Adjust & Resubmit Proposal</h3>
                <p>This proposal was returned to you. Apply the suggested changes and resubmit to the Treasurer.</p>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="resubmit">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Event Title</label>
                            <input type="text" id="title" name="title"
                                   value="<?php echo htmlspecialchars($proposal['title']); ?>" required>
                        </div>

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

                        <div class="form-group">
                            <label for="expected_participants">Expected Participants</label>
                            <input type="number" id="expected_participants" name="expected_participants"
                                   value="<?php echo (int)$proposal['expected_participants']; ?>" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="proposed_budget">Proposed Budget (₱)</label>
                            <input type="number" step="0.01" min="0" id="proposed_budget" name="proposed_budget"
                                   value="<?php echo htmlspecialchars($proposal['proposed_budget']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" required><?php
                            echo htmlspecialchars($proposal['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="objectives">Objectives</label>
                        <textarea id="objectives" name="objectives" rows="3" required><?php
                            echo htmlspecialchars($proposal['objectives']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="activities">Activities</label>
                        <textarea id="activities" name="activities" rows="3" required><?php
                            echo htmlspecialchars($proposal['activities']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="expected_outcomes">Expected Outcomes</label>
                        <textarea id="expected_outcomes" name="expected_outcomes" rows="3" required><?php
                            echo htmlspecialchars($proposal['expected_outcomes']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="budget_breakdown">Budget Breakdown</label>
                        <textarea id="budget_breakdown" name="budget_breakdown" rows="4" required><?php
                            echo htmlspecialchars($proposal['budget_breakdown']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Resubmit this adjusted proposal to the Treasurer?');">
                            Resubmit to Treasurer
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="detail-card">
                <h3>Edit Locked</h3>
                <p>This proposal is either still under review or already finalized. You can no longer edit it.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
