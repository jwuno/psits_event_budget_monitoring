<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

$proposal_id = $_GET['id'] ?? null;

if (!$proposal_id) {
    $_SESSION['error'] = "No proposal specified";
    header("Location: review_proposals.php");
    exit;
}

// Get proposal details
$sql = "SELECT * FROM proposals WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $proposal_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$proposal = mysqli_fetch_assoc($result);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found";
    header("Location: review_proposals.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $treasurer_remarks = mysqli_real_escape_string($conn, $_POST['treasurer_remarks']);
    $action = $_POST['action']; // approve or reject
    
    if ($action == 'approve') {
        // Approve and send to president
        $update_sql = "UPDATE proposals SET 
            status = 'approved',
            president_remarks = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $notification_recipient = 'president';
        $notification_message = "Proposal budget approved by Treasurer: " . $proposal['title'] . " - ₱" . number_format($proposal['proposed_budget'], 2) . " - Ready for President approval";
        $success_message = "Budget approved! Proposal sent to President for final approval.";
    } else {
        // Reject and send back to secretary
        $update_sql = "UPDATE proposals SET 
            status = 'rejected',
            president_remarks = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $notification_recipient = 'secretary';
        $notification_message = "Proposal budget rejected by Treasurer: " . $proposal['title'] . " - Please adjust the budget";
        $success_message = "Budget rejected! Proposal returned to Secretary for budget adjustment.";
    }
    
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $treasurer_remarks, $proposal_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        // Add notification
        include('../../includes/functions.php');
        addNotification($conn, $notification_recipient, $notification_message);
        
        $_SESSION['success'] = $success_message;
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Error updating proposal: " . mysqli_error($conn);
    }
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="header-with-back">
            <a href="review_proposals.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Reviews
            </a>
            <div>
                <h1>Budget Review</h1>
                <p>Review and approve/reject proposal budget</p>
            </div>
        </div>
    </div>

    <div class="review-container">
        <!-- Proposal Details -->
        <div class="proposal-details-card">
            <h3>Proposal Details</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Title:</strong>
                    <span><?php echo htmlspecialchars($proposal['title']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Event Date:</strong>
                    <span><?php echo $proposal['event_date'] ? date('M j, Y', strtotime($proposal['event_date'])) : 'Not set'; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Venue:</strong>
                    <span><?php echo htmlspecialchars($proposal['venue']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Expected Participants:</strong>
                    <span><?php echo $proposal['expected_participants']; ?> students</span>
                </div>
                <div class="detail-item">
                    <strong>Proposed Budget:</strong>
                    <span class="budget-amount">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Cost per Participant:</strong>
                    <span>₱<?php echo number_format($proposal['proposed_budget'] / max(1, $proposal['expected_participants']), 2); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Submitted By:</strong>
                    <span><?php echo htmlspecialchars($proposal['created_by']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Submitted On:</strong>
                    <span><?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></span>
                </div>
            </div>

            <?php if (!empty($proposal['objectives'])): ?>
            <div class="section">
                <h4>Objectives</h4>
                <div class="section-content">
                    <?php echo nl2br(htmlspecialchars($proposal['objectives'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($proposal['description'])): ?>
            <div class="section">
                <h4>Event Description</h4>
                <div class="section-content">
                    <?php echo nl2br(htmlspecialchars($proposal['description'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($proposal['activities'])): ?>
            <div class="section">
                <h4>Activities & Program</h4>
                <div class="section-content">
                    <?php echo nl2br(htmlspecialchars($proposal['activities'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($proposal['budget_breakdown'])): ?>
            <div class="section">
                <h4>Budget Breakdown</h4>
                <div class="section-content budget-breakdown">
                    <?php echo nl2br(htmlspecialchars($proposal['budget_breakdown'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Treasurer Review Form -->
        <div class="review-form-card">
            <h3>Budget Assessment</h3>
            <form method="POST" class="review-form">
                <div class="form-group">
                    <label for="treasurer_remarks">Financial Assessment & Remarks *</label>
                    <textarea id="treasurer_remarks" name="treasurer_remarks" rows="8" required 
                              placeholder="Provide your detailed financial assessment:

• Is the budget reasonable for this event?
• Are the cost allocations appropriate?
• Any recommended adjustments?
• Cost efficiency analysis
• Any concerns or questions..."></textarea>
                    <small>Your remarks will be visible to the President and Secretary</small>
                </div>

                <div class="assessment-questions">
                    <h4>Budget Assessment Questions</h4>
                    <div class="question">
                        <strong>1. Is the total budget appropriate for this event?</strong>
                        <p>Consider the scale, participants, and activities planned.</p>
                    </div>
                    <div class="question">
                        <strong>2. Are the budget allocations reasonable?</strong>
                        <p>Check if each category (food, materials, etc.) has appropriate funding.</p>
                    </div>
                    <div class="question">
                        <strong>3. Is the cost per participant reasonable?</strong>
                        <p>Current: ₱<?php echo number_format($proposal['proposed_budget'] / max(1, $proposal['expected_participants']), 2); ?> per participant</p>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="action" value="approve" class="btn btn-success btn-large">
                        <i class="fas fa-check-circle"></i> Approve Budget
                    </button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-large">
                        <i class="fas fa-times-circle"></i> Reject Budget
                    </button>
                    <a href="review_proposals.php" class="btn btn-secondary">Cancel Review</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.review-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 20px;
}

.proposal-details-card,
.review-form-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.detail-grid {
    display: grid;
    gap: 12px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.detail-item:last-child {
    border-bottom: none;
}

.budget-amount {
    color: #28a745;
    font-weight: 700;
    font-size: 16px;
}

.section {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.section h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 16px;
}

.section-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    line-height: 1.5;
}

.budget-breakdown {
    white-space: pre-wrap;
    font-family: monospace;
}

.review-form textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    resize: vertical;
    line-height: 1.5;
    font-family: inherit;
}

.review-form textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.assessment-questions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 4px solid #007bff;
}

.assessment-questions h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.question {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #dee2e6;
}

.question:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.question strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 5px;
}

.question p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.form-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 25px;
}

.btn-large {
    padding: 15px 20px;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-success {
    background: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background: #218838;
    border-color: #1e7e34;
}

.btn-danger {
    background: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
}
</style>

<?php include('../../includes/footer.php'); ?>