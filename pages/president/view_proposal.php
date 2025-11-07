<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No proposal specified!";
    header("Location: dashboard.php");
    exit;
}

$proposal_id = $_GET['id'];
$query = "SELECT * FROM proposals WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $proposal_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$proposal = mysqli_fetch_assoc($result);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found!";
    header("Location: dashboard.php");
    exit;
}
?>

<div class="dashboard-container">
    <div class="proposal-detail-header">
        <div class="header-content">
            <h1><?php echo htmlspecialchars($proposal['title']); ?></h1>
            <div class="proposal-meta">
                <span class="status-badge <?php echo strtolower($proposal['status']); ?>">
                    <?php echo $proposal['status']; ?>
                </span>
                <span class="submission-date">
                    Submitted by <?php echo htmlspecialchars($proposal['created_by']); ?> on <?php echo date('F j, Y', strtotime($proposal['date_submitted'])); ?>
                </span>
            </div>
        </div>
        
        <?php if($proposal['status'] == 'Pending'): ?>
        <div class="action-buttons">
            <form action="approve_proposal.php" method="POST" style="display: inline;">
                <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                <button type="submit" class="btn btn-approve-large" onclick="return confirm('Approve this proposal?')">
                    <i class="fas fa-check"></i> Approve Proposal
                </button>
            </form>
            <form action="reject_proposal.php" method="POST" style="display: inline;">
                <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                <button type="submit" class="btn btn-reject-large" onclick="return confirm('Reject this proposal?')">
                    <i class="fas fa-times"></i> Reject Proposal
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Same content as secretary view but with approval buttons -->
    <div class="proposal-detail-grid">
        <!-- Basic Information -->
        <div class="detail-card">
            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
            <div class="detail-group">
                <label>Proposed By:</label>
                <span><?php echo htmlspecialchars($proposal['created_by']); ?></span>
            </div>
            <div class="detail-group">
                <label>Event Date:</label>
                <span><?php echo date('F j, Y', strtotime($proposal['event_date'])); ?></span>
            </div>
            <div class="detail-group">
                <label>Venue:</label>
                <span><?php echo htmlspecialchars($proposal['venue']); ?></span>
            </div>
            <div class="detail-group">
                <label>Expected Participants:</label>
                <span><?php echo $proposal['expected_participants']; ?> people</span>
            </div>
        </div>

        <!-- Budget Information -->
        <div class="detail-card">
            <h3><i class="fas fa-money-bill-wave"></i> Budget Information</h3>
            <div class="detail-group">
                <label>Proposed Budget:</label>
                <span class="budget-amount">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
            </div>
            <div class="detail-group">
                <label>Budget Breakdown:</label>
                <div class="budget-breakdown">
                    <?php echo nl2br(htmlspecialchars($proposal['budget_breakdown'])); ?>
                </div>
            </div>
        </div>

        <!-- Proposal Details -->
        <div class="detail-card full-width">
            <h3><i class="fas fa-file-alt"></i> Proposal Details</h3>
            <div class="detail-group">
                <label>Objectives:</label>
                <div class="proposal-content">
                    <?php echo nl2br(htmlspecialchars($proposal['objectives'])); ?>
                </div>
            </div>
            <div class="detail-group">
                <label>Event Description:</label>
                <div class="proposal-content">
                    <?php echo nl2br(htmlspecialchars($proposal['description'])); ?>
                </div>
            </div>
            <div class="detail-group">
                <label>Activities & Program:</label>
                <div class="proposal-content">
                    <?php echo nl2br(htmlspecialchars($proposal['activities'])); ?>
                </div>
            </div>
            <div class="detail-group">
                <label>Expected Outcomes:</label>
                <div class="proposal-content">
                    <?php echo nl2br(htmlspecialchars($proposal['expected_outcomes'])); ?>
                </div>
            </div>
        </div>

        <!-- Attachments -->
        <?php if($proposal['attachment_path']): ?>
        <div class="detail-card">
            <h3><i class="fas fa-paperclip"></i> Attachments</h3>
            <div class="detail-group">
                <label>Supporting Document:</label>
                <div class="attachment-link">
                    <a href="../../assets/uploads/<?php echo $proposal['attachment_path']; ?>" target="_blank" class="btn btn-download">
                        <i class="fas fa-download"></i> Download Attachment
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="navigation-actions">
        <a href="pending_proposals.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Pending Proposals
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>