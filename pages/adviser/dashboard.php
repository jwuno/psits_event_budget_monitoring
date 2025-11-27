<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'adviser') {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// ================== HANDLE FINAL APPROVE / REJECT ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id'], $_POST['action'])) {
    $proposal_id     = (int) $_POST['proposal_id'];
    $action          = $_POST['action'];
    $adviser_remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');

    if ($proposal_id > 0 && in_array($action, ['approve', 'reject'], true)) {

        if ($action === 'approve') {
            $sql = "UPDATE proposals SET
                        status              = 'approved',
                        current_stage       = 'final',
                        adviser_status      = 'approved',
                        adviser_remarks     = '$adviser_remarks',
                        adviser_reviewed_at = NOW(),
                        adviser_review_date = NOW(),
                        adviser_reviewed_by = '{$_SESSION['full_name']}',
                        reviewed_by         = '{$_SESSION['full_name']}',
                        review_date         = NOW()
                    WHERE id = $proposal_id AND current_stage = 'adviser'";
            $success_msg = "Proposal approved successfully!";
        } else {
            $sql = "UPDATE proposals SET
                        status              = 'rejected',
                        current_stage       = 'final',
                        adviser_status      = 'rejected',
                        adviser_remarks     = '$adviser_remarks',
                        adviser_reviewed_at = NOW(),
                        adviser_review_date = NOW(),
                        adviser_reviewed_by = '{$_SESSION['full_name']}',
                        rejection_reason    = '$adviser_remarks',
                        reviewed_by         = '{$_SESSION['full_name']}',
                        review_date         = NOW()
                    WHERE id = $proposal_id AND current_stage = 'adviser'";
            $success_msg = "Proposal rejected successfully!";
        }

        if (mysqli_query($conn, $sql)) {
            // Notifications for officers
            $info_res = mysqli_query($conn, "SELECT title, created_by FROM proposals WHERE id = $proposal_id");
            $info     = mysqli_fetch_assoc($info_res);
            $ptitle   = $info['title'] ?? 'an event';

            if ($action === 'approve') {
                $notif_message = "Proposal \"$ptitle\" has been APPROVED by the Adviser.";
            } else {
                $notif_message = "Proposal \"$ptitle\" has been REJECTED by the Adviser.";
            }

            addNotification($conn, 'secretary', $notif_message, $_SESSION['full_name']);
            addNotification($conn, 'treasurer', $notif_message, $_SESSION['full_name']);
            addNotification($conn, 'president', $notif_message, $_SESSION['full_name']);

            $_SESSION['success'] = $success_msg;
        } else {
            $_SESSION['error'] = "Error updating proposal: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Invalid request.";
    }

    header("Location: dashboard.php");
    exit;
}

// ================== FETCH DASHBOARD DATA ==================

// Summary counts
$counts_sql = "
    SELECT
        COUNT(*) AS total_proposals,
        SUM(CASE WHEN status = 'pending' AND current_stage = 'adviser' THEN 1 ELSE 0 END) AS pending_adviser,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
    FROM proposals
";
$counts_res = mysqli_query($conn, $counts_sql);
$counts     = mysqli_fetch_assoc($counts_res);

// Pending for Adviser
$pending_sql = "
    SELECT *
    FROM proposals
    WHERE status = 'pending' AND current_stage = 'adviser'
    ORDER BY date_submitted DESC
";
$pending_res       = mysqli_query($conn, $pending_sql);
$pending_proposals = mysqli_fetch_all($pending_res, MYSQLI_ASSOC);

include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Adviser Dashboard</h1>
            <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>! Final review and approval of event proposals.</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo (int)($counts['pending_adviser'] ?? 0); ?></h3>
                <p>Pending Final Approval</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <h3><?php echo (int)($counts['total_proposals'] ?? 0); ?></h3>
                <p>Total Proposals</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo (int)($counts['approved'] ?? 0); ?></h3>
                <p>Approved Proposals</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-info">
                <h3><?php echo (int)($counts['rejected'] ?? 0); ?></h3>
                <p>Rejected Proposals</p>
            </div>
        </div>
    </div>

    <!-- Pending for Adviser Section -->
    <div class="proposals-section">
        <div class="section-header">
            <h2>Proposals Awaiting Your Decision</h2>
            <p>These proposals have passed Treasurer and President, and are now waiting for your final approval.</p>
        </div>

        <div class="proposals-grid">
            <?php if (!empty($pending_proposals)): ?>
                <?php foreach ($pending_proposals as $proposal): ?>
                    <div class="proposal-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($proposal['title']); ?></h3>
                            <span class="status-badge status-pending">Pending (Adviser)</span>
                        </div>

                        <div class="card-body">
                            <div class="proposal-meta">
                                <div class="meta-item">
                                    <strong>Event Date:</strong>
                                    <span><?php echo date('M j, Y', strtotime($proposal['event_date'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <strong>Venue:</strong>
                                    <span><?php echo htmlspecialchars($proposal['venue']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <strong>Participants:</strong>
                                    <span><?php echo (int)$proposal['expected_participants']; ?> students</span>
                                </div>
                                <div class="meta-item">
                                    <strong>Proposed Budget:</strong>
                                    <span>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                                </div>
                                <div class="meta-item">
                                    <strong>Created by:</strong>
                                    <span><?php echo htmlspecialchars($proposal['created_by']); ?></span>
                                </div>
                            </div>

                            <?php if (!empty($proposal['objectives'])): ?>
                                <div class="detail-section">
                                    <h4>Objectives</h4>
                                    <p><?php echo nl2br(htmlspecialchars($proposal['objectives'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($proposal['budget_breakdown'])): ?>
                                <div class="detail-section">
                                    <h4>Budget Breakdown</h4>
                                    <p><?php echo nl2br(htmlspecialchars($proposal['budget_breakdown'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($proposal['treasurer_remarks'])): ?>
                                <div class="detail-section">
                                    <h4>Treasurer Remarks</h4>
                                    <p><?php echo nl2br(htmlspecialchars($proposal['treasurer_remarks'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($proposal['president_remarks'])): ?>
                                <div class="detail-section">
                                    <h4>President Remarks</h4>
                                    <p><?php echo nl2br(htmlspecialchars($proposal['president_remarks'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-actions">
                            <form method="POST" class="review-form">
                                <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">

                                <div class="form-group">
                                    <label for="remarks_<?php echo $proposal['id']; ?>">Your Remarks (optional)</label>
                                    <textarea
                                        id="remarks_<?php echo $proposal['id']; ?>"
                                        name="remarks"
                                        rows="2"
                                        placeholder="Add final remarks or reasons for approval/rejection..."
                                    ></textarea>
                                </div>

                                <div class="button-group">
                                    <button type="submit" name="action" value="approve" class="btn btn-primary"
                                            onclick="return confirm('Approve this proposal as FINAL?');">
                                        ✅ Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger"
                                            onclick="return confirm('Reject this proposal as FINAL?');">
                                        ❌ Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">✅</div>
                    <h4>All Caught Up!</h4>
                    <p>No proposals are currently waiting for your final approval.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
