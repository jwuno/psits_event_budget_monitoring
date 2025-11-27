<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

$proposal_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;

if (!$proposal_id) {
    $_SESSION['error'] = "No proposal specified!";
    header("Location: pending_proposals.php");
    exit;
}

// Info for notifications
$info_res = mysqli_query($conn, "SELECT title, created_by FROM proposals WHERE id = '$proposal_id'");
$info     = mysqli_fetch_assoc($info_res);
$prop_title   = $info['title'] ?? 'an event';
$prop_creator = $info['created_by'] ?? 'the secretary';

// HANDLE APPROVE / RETURN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action            = $_POST['action'];
    $president_remarks = mysqli_real_escape_string($conn, $_POST['president_remarks']);

    if ($action === 'approve') {
        $sql = "UPDATE proposals SET
                    status                 = 'pending',
                    current_stage          = 'adviser',
                    president_status       = 'approved',
                    president_remarks      = '$president_remarks',
                    president_approved_at  = NOW(),
                    president_review_date  = NOW(),
                    president_reviewed_by  = '{$_SESSION['full_name']}',
                    reviewed_by            = '{$_SESSION['full_name']}',
                    review_date            = NOW(),
                    returned_from          = NULL
                WHERE id = '$proposal_id'";

        if (mysqli_query($conn, $sql)) {
            $by = $_SESSION['full_name'] ?? 'President';

            $msg_adv = "Proposal \"$prop_title\" was approved by $by and is ready for your final review.";
            addNotification($conn, 'adviser', $msg_adv, $by);

            $msg_sec = "Your proposal \"$prop_title\" was approved by $by and sent to the Adviser for final approval.";
            addNotification($conn, 'secretary', $msg_sec, $by);

            $msg_treas = "Proposal \"$prop_title\" you reviewed earlier has been approved by $by.";
            addNotification($conn, 'treasurer', $msg_treas, $by);

            $_SESSION['success'] = "Proposal forwarded to Adviser for final review.";
        } else {
            $_SESSION['error'] = "Error updating proposal: " . mysqli_error($conn);
        }

        header("Location: pending_proposals.php");
        exit;

    } elseif ($action === 'reject') {
        $sql = "UPDATE proposals SET
                    status                 = 'returned',
                    current_stage          = 'treasurer',
                    returned_from          = 'president',
                    president_status       = 'rejected',
                    president_remarks      = '$president_remarks',
                    president_review_date  = NOW(),
                    president_reviewed_by  = '{$_SESSION['full_name']}',
                    rejection_reason       = '$president_remarks',
                    reviewed_by            = '{$_SESSION['full_name']}',
                    review_date            = NOW()
                WHERE id = '$proposal_id'";

        if (mysqli_query($conn, $sql)) {
            $by  = $_SESSION['full_name'] ?? 'President';
            $msg = "Proposal \"$prop_title\" was returned by $by for further budget adjustments.";
            addNotification($conn, 'treasurer', $msg, $by);

            $_SESSION['success'] = "Proposal returned to Treasurer for budget adjustment.";
        } else {
            $_SESSION['error'] = "Error updating proposal: " . mysqli_error($conn);
        }

        header("Location: pending_proposals.php");
        exit;
    }
}

// FETCH PROPOSAL
$proposal_sql    = "SELECT * FROM proposals WHERE id = '$proposal_id'";
$proposal_result = mysqli_query($conn, $proposal_sql);
$proposal        = mysqli_fetch_assoc($proposal_result);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found!";
    header("Location: pending_proposals.php");
    exit;
}

include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Review Proposal (President)</h1>
            <p>Review this proposal and decide whether to forward it to the Adviser or return it to the Treasurer.</p>
        </div>
        <div class="header-actions">
            <a href="pending_proposals.php" class="btn-back">← Back to Pending Proposals</a>
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
                    <span class="budget-amount">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Created by:</strong>
                    <span><?php echo htmlspecialchars($proposal['created_by']); ?></span>
                </div>
            </div>

            <?php if (!empty($proposal['budget_breakdown'])): ?>
                <div class="detail-section">
                    <h3>Budget Breakdown</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['budget_breakdown'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['objectives'])): ?>
                <div class="detail-section">
                    <h3>Objectives</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['objectives'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['activities'])): ?>
                <div class="detail-section">
                    <h3>Activities</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['activities'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['expected_outcomes'])): ?>
                <div class="detail-section">
                    <h3>Expected Outcomes</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['expected_outcomes'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['description'])): ?>
                <div class="detail-section">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['description'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['treasurer_remarks'])): ?>
                <div class="detail-section">
                    <h3>Treasurer Remarks</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['treasurer_remarks'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['attachment_path'])): ?>
                <div class="detail-section">
                    <h3>Attachment</h3>
                    <a href="../../uploads/<?php echo htmlspecialchars($proposal['attachment_path']); ?>"
                       class="btn-download" target="_blank">
                        View Attachment
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($proposal['status'] === 'pending' && $proposal['current_stage'] === 'president'): ?>
            <div class="action-card">
                <h3>President’s Decision</h3>

                <!-- Approve -> Adviser -->
                <form method="POST" action="" class="action-form">
                    <input type="hidden" name="action" value="approve">

                    <div class="form-group">
                        <label for="president_remarks_approve">Remarks / Notes (optional):</label>
                        <textarea id="president_remarks_approve" name="president_remarks"
                                  placeholder="Add your remarks before forwarding to Adviser..."><?php 
                                  echo htmlspecialchars($proposal['president_remarks'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-approve-large"
                                onclick="return confirm('Approve this proposal and forward to the Adviser?');">
                            Approve &amp; Forward to Adviser
                        </button>
                    </div>
                </form>

                <!-- Return -> Treasurer -->
                <form method="POST" action="" class="reject-form">
                    <input type="hidden" name="action" value="reject">

                    <div class="form-group">
                        <label for="president_remarks_reject">Reason for Returning to Treasurer:</label>
                        <textarea id="president_remarks_reject" name="president_remarks"
                                  placeholder="Explain what needs to be adjusted by the Treasurer..."
                                  required rows="4"><?php 
                                  echo htmlspecialchars($proposal['president_remarks'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-reject-large"
                                onclick="return confirm('Return this proposal to Treasurer for adjustment?');">
                            Return to Treasurer
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="detail-card">
                <h3>Review Summary</h3>
                <p><strong>Status:</strong> <?php echo ucfirst($proposal['status']); ?></p>
                <?php if (!empty($proposal['president_remarks'])): ?>
                    <p><strong>President Remarks:</strong> <?php echo nl2br(htmlspecialchars($proposal['president_remarks'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($proposal['president_reviewed_by'])): ?>
                    <p><strong>Reviewed by:</strong> <?php echo htmlspecialchars($proposal['president_reviewed_by']); ?></p>
                <?php endif; ?>
                <?php if (!empty($proposal['president_review_date'])): ?>
                    <p><strong>Review Date:</strong> <?php echo date('M j, Y g:i A', strtotime($proposal['president_review_date'])); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
