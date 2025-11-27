<?php
// ================== ACCESS CONTROL & SETUP ==================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Get proposal ID
$proposal_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;

if (!$proposal_id) {
    $_SESSION['error'] = "No proposal specified!";
    header("Location: pending_reviews.php");
    exit;
}

// Basic info for notifications
$info_sql = "SELECT title, created_by FROM proposals WHERE id = '$proposal_id'";
$info_res = mysqli_query($conn, $info_sql);
$info     = mysqli_fetch_assoc($info_res);
$prop_title   = $info['title'] ?? 'an event';
$prop_creator = $info['created_by'] ?? 'the secretary';

// ================== HANDLE ACTIONS (APPROVE / RETURN) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Treasurer approves & forwards to President
        $adjusted_budget = mysqli_real_escape_string($conn, $_POST['adjusted_budget']);
        $budget_notes    = mysqli_real_escape_string($conn, $_POST['budget_notes']);

        $update_sql = "UPDATE proposals SET 
                status                = 'pending',
                current_stage         = 'president',
                treasurer_status      = 'approved',
                treasurer_remarks     = '$budget_notes',
                treasurer_reviewed_at = NOW(),
                proposed_budget       = '$adjusted_budget',
                budget_notes          = '$budget_notes',
                returned_from         = NULL,
                reviewed_by           = '{$_SESSION['full_name']}',
                review_date           = NOW()
            WHERE id = '$proposal_id'";

        if (mysqli_query($conn, $update_sql)) {
            $by = $_SESSION['full_name'] ?? 'Treasurer';

            // Notify President
            $msg_pres = "Proposal \"$prop_title\" was approved by $by and is ready for your review.";
            addNotification($conn, 'president', $msg_pres, $by);

            // Notify Secretary
            $msg_sec = "Your proposal \"$prop_title\" passed the Treasurer review and is now with the President.";
            addNotification($conn, 'secretary', $msg_sec, $by);

            $_SESSION['success'] = 'Proposal forwarded to President for approval.';
        } else {
            $_SESSION['error'] = 'Error updating proposal: ' . mysqli_error($conn);
        }

        header("Location: pending_reviews.php");
        exit;

    } elseif ($action === 'reject') {
        // Treasurer returns to Secretary
        $rejection_reason = mysqli_real_escape_string($conn, $_POST['rejection_reason']);

        $update_sql = "UPDATE proposals SET 
                status                = 'returned',
                current_stage         = 'secretary',
                treasurer_status      = 'rejected',
                treasurer_remarks     = '$rejection_reason',
                treasurer_reviewed_at = NOW(),
                rejection_reason      = '$rejection_reason',
                reviewed_by           = '{$_SESSION['full_name']}',
                review_date           = NOW()
            WHERE id = '$proposal_id'";

        if (mysqli_query($conn, $update_sql)) {
            $by  = $_SESSION['full_name'] ?? 'Treasurer';
            $msg = "Your proposal \"$prop_title\" was returned by $by for budget adjustments.";
            addNotification($conn, 'secretary', $msg, $by);

            $_SESSION['success'] = 'Proposal returned to Secretary for adjustment.';
        } else {
            $_SESSION['error'] = 'Error updating proposal: ' . mysqli_error($conn);
        }

        header("Location: pending_reviews.php");
        exit;
    }
}

// ================== FETCH PROPOSAL FOR DISPLAY ==================
$proposal_sql    = "SELECT * FROM proposals WHERE id = '$proposal_id'";
$proposal_result = mysqli_query($conn, $proposal_sql);
$proposal        = mysqli_fetch_assoc($proposal_result);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found!";
    header("Location: pending_reviews.php");
    exit;
}

// Can Treasurer act? (new OR returned by President)
$can_act = ($proposal['current_stage'] === 'treasurer' 
            && in_array($proposal['status'], ['pending', 'returned']));

include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Review Proposal (Treasurer)</h1>
            <p>Review and adjust the event budget before passing it to the President.</p>
        </div>
        <div class="header-actions">
            <a href="pending_reviews.php" class="btn-back">← Back to Pending Reviews</a>
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

            <?php if (!empty($proposal['president_remarks'])): ?>
                <div class="detail-section">
                    <h3>President Remarks</h3>
                    <p><?php echo nl2br(htmlspecialchars($proposal['president_remarks'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['treasurer_remarks'])): ?>
                <div class="detail-section">
                    <h3>Your Previous Remarks</h3>
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

        <?php if ($can_act): ?>
            <div class="action-card">
                <?php if ($proposal['status'] === 'returned' && $proposal['returned_from'] === 'president'): ?>
                    <h3>Adjust Budget & Forward Back to President</h3>
                    <p>This proposal was returned by the President. Adjust the budget based on the remarks, then send it back.</p>

                    <form method="POST" action="" class="action-form">
                        <input type="hidden" name="action" value="approve">

                        <div class="form-group">
                            <label for="adjusted_budget">Adjusted Budget Amount:</label>
                            <input type="number" id="adjusted_budget" name="adjusted_budget"
                                   value="<?php echo $proposal['proposed_budget']; ?>" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="budget_notes">Treasurer Notes (for President):</label>
                            <textarea id="budget_notes" name="budget_notes"
                                      placeholder="Explain any adjustments you made based on the President's remarks..."><?php 
                                      echo htmlspecialchars($proposal['treasurer_remarks'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-approve-large"
                                    onclick="return confirm('Forward this adjusted proposal back to the President?');">
                                Forward Adjusted Budget to President
                            </button>
                        </div>
                    </form>

                <?php else: ?>
                    <h3>Budget Review & Action</h3>

                    <!-- Approve & Forward to President -->
                    <form method="POST" action="" class="action-form">
                        <input type="hidden" name="action" value="approve">

                        <div class="form-group">
                            <label for="adjusted_budget">Adjusted Budget Amount:</label>
                            <input type="number" id="adjusted_budget" name="adjusted_budget"
                                   value="<?php echo $proposal['proposed_budget']; ?>" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="budget_notes">Budget Notes (Optional):</label>
                            <textarea id="budget_notes" name="budget_notes"
                                      placeholder="Explain your budget decision or conditions..."><?php 
                                      echo htmlspecialchars($proposal['treasurer_remarks'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-approve-large"
                                    onclick="return confirm('Approve this proposal and forward to the President?');">
                                Approve &amp; Forward to President
                            </button>
                        </div>
                    </form>

                    <!-- Return to Secretary -->
                    <form method="POST" action="" class="reject-form">
                        <input type="hidden" name="action" value="reject">

                        <div class="form-group">
                            <label for="rejection_reason">Reason for Returning to Secretary:</label>
                            <textarea id="rejection_reason" name="rejection_reason"
                                      placeholder="Explain why this proposal needs adjustment..."
                                      required rows="4"><?php 
                                      echo htmlspecialchars($proposal['treasurer_remarks'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-reject-large"
                                    onclick="return confirm('Return this proposal to the Secretary for adjustment?');">
                                Return to Secretary
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="detail-card">
                <h3>Review Summary</h3>
                <p><strong>Status:</strong> <?php echo ucfirst($proposal['status']); ?></p>
                <?php if (!empty($proposal['reviewed_by'])): ?>
                    <p><strong>Last Reviewed by:</strong> <?php echo htmlspecialchars($proposal['reviewed_by']); ?></p>
                <?php endif; ?>
                <?php if (!empty($proposal['review_date']) && $proposal['review_date'] !== '0000-00-00 00:00:00'): ?>
                    <p><strong>Review Date:</strong> <?php echo date('M j, Y g:i A', strtotime($proposal['review_date'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($proposal['treasurer_remarks'])): ?>
                    <p><strong>Treasurer Remarks:</strong> <?php echo nl2br(htmlspecialchars($proposal['treasurer_remarks'])); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
