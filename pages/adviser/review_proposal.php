<?php
// ===== ACCESS CONTROL & SETUP (NO OUTPUT YET) =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'adviser') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get proposal ID
$proposal_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;

if (!$proposal_id) {
    $_SESSION['error'] = "No proposal specified!";
    header("Location: pending_proposals.php");
    exit;
}

// ===== HANDLE FORM SUBMISSION (FINAL APPROVE / REJECT) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action           = $_POST['action'];
    $adviser_remarks  = mysqli_real_escape_string($conn, $_POST['adviser_remarks']);

    if ($action === 'approve') {
        // FINAL APPROVAL
        $sql = "UPDATE proposals SET
                    status                = 'approved',
                    current_stage         = 'final',

                    adviser_status        = 'approved',
                    adviser_remarks       = '$adviser_remarks',
                    adviser_reviewed_at   = NOW(),
                    adviser_review_date   = NOW(),
                    adviser_reviewed_by   = '{$_SESSION['full_name']}',

                    reviewed_by           = '{$_SESSION['full_name']}',
                    review_date           = NOW()
                WHERE id = '$proposal_id'";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Proposal APPROVED. Status is now FINAL.";
        } else {
            $_SESSION['error'] = "Error updating proposal: " . mysqli_error($conn);
        }

        header("Location: pending_proposals.php");
        exit;

    } elseif ($action === 'reject') {
        // FINAL REJECTION (only adviser can fully reject)
        $sql = "UPDATE proposals SET
                    status                = 'rejected',
                    current_stage         = 'final',

                    adviser_status        = 'rejected',
                    adviser_remarks       = '$adviser_remarks',
                    adviser_reviewed_at   = NOW(),
                    adviser_review_date   = NOW(),
                    adviser_reviewed_by   = '{$_SESSION['full_name']}',

                    rejection_reason      = '$adviser_remarks',
                    reviewed_by           = '{$_SESSION['full_name']}',
                    review_date           = NOW()
                WHERE id = '$proposal_id'";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Proposal REJECTED. Status is now FINAL.";
        } else {
            $_SESSION['error'] = "Error updating proposal: " . mysqli_error($conn);
        }

        header("Location: pending_proposals.php");
        exit;
    }
}

// ===== FETCH PROPOSAL FOR DISPLAY =====
$proposal_sql    = "SELECT * FROM proposals WHERE id = '$proposal_id'";
$proposal_result = mysqli_query($conn, $proposal_sql);
$proposal        = mysqli_fetch_assoc($proposal_result);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found!";
    header("Location: pending_proposals.php");
    exit;
}

// Safe to output HTML now
include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Final Review</h1>
            <p>Adviser’s final decision on the proposal.</p>
        </div>
        <div class="header-actions">
            <a href="pending_proposals.php" class="btn-back">← Back to Pending Proposals</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="proposal-detail">
        <div class="proposal-card">
            <div class="proposal-header">
                <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                <span class="status-badge status-<?php echo strtolower($proposal['status']); ?>">
                    <?php echo ucfirst($proposal['status']); ?>
                </span>
            </div>

            <div class="proposal-details">
                <div class="detail-item">
                    <strong>Participants:</strong>
                    <span><?php echo $proposal['expected_participants']; ?> students</span>
                </div>
                <div class="detail-item">
                    <strong>Created by:</strong>
                    <span><?php echo htmlspecialchars($proposal['created_by']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Event Date:</strong>
                    <span><?php echo date('M j, Y', strtotime($proposal['event_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Venue:</strong>
                    <span><?php echo htmlspecialchars($proposal['venue']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Final Budget:</strong>
                    <span class="budget-amount">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
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

        <?php if ($proposal['status'] === 'pending' && $proposal['current_stage'] === 'adviser'): ?>
            <!-- Final decision form -->
            <div class="action-card">
                <h3>Adviser’s Final Decision</h3>

                <!-- Final Approve -->
                <form method="POST" action="" class="action-form">
                    <input type="hidden" name="action" value="approve">

                    <div class="form-group">
                        <label for="adviser_remarks_approve">Remarks (optional):</label>
                        <textarea id="adviser_remarks_approve" name="adviser_remarks"
                                  placeholder="Optional comments for the final approval..."><?php
                                  echo htmlspecialchars($proposal['adviser_remarks'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-approve-large"
                                onclick="return confirm('Approve this proposal as FINAL?');">
                            Approve (Final)
                        </button>
                    </div>
                </form>

                <!-- Final Reject -->
                <form method="POST" action="" class="reject-form">
                    <input type="hidden" name="action" value="reject">

                    <div class="form-group">
                        <label for="adviser_remarks_reject">Reason for Rejection:</label>
                        <textarea id="adviser_remarks_reject" name="adviser_remarks"
                                  placeholder="Explain why this proposal is being rejected..."
                                  required rows="4"><?php
                                  echo htmlspecialchars($proposal['adviser_remarks'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-reject-large"
                                onclick="return confirm('Reject this proposal as FINAL? This cannot be undone.');">
                            Reject (Final)
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Read-only -->
            <div class="detail-card">
                <h3>Final Decision Summary</h3>
                <p><strong>Status:</strong> <?php echo ucfirst($proposal['status']); ?></p>
                <?php if (!empty($proposal['adviser_remarks'])): ?>
                    <p><strong>Adviser Remarks:</strong>
                        <?php echo nl2br(htmlspecialchars($proposal['adviser_remarks'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($proposal['adviser_reviewed_by'])): ?>
                    <p><strong>Reviewed by:</strong>
                        <?php echo htmlspecialchars($proposal['adviser_reviewed_by']); ?></p>
                <?php endif; ?>
                <?php if (!empty($proposal['adviser_review_date'])): ?>
                    <p><strong>Review Date:</strong>
                        <?php echo date('M j, Y g:i A', strtotime($proposal['adviser_review_date'])); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
