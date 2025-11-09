<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get proposal ID and source from URL
$proposal_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;
$source = isset($_GET['source']) ? $_GET['source'] : 'dashboard';

if (!$proposal_id) {
    $_SESSION['error'] = "No proposal specified!";
    header("Location: dashboard.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $form_source = isset($_POST['source']) ? $_POST['source'] : 'dashboard';
    
    if ($action == 'approve') {
        $adjusted_budget = mysqli_real_escape_string($conn, $_POST['adjusted_budget']);
        $budget_notes = mysqli_real_escape_string($conn, $_POST['budget_notes']);
        
        $update_sql = "UPDATE proposals SET 
            status = 'approved',
            proposed_budget = '$adjusted_budget',
            budget_notes = '$budget_notes',
            reviewed_by = '{$_SESSION['full_name']}', 
            review_date = NOW() 
            WHERE id = '$proposal_id'";
        $_SESSION['success'] = "Proposal approved successfully!";
        
    } elseif ($action == 'reject') {
        $rejection_reason = mysqli_real_escape_string($conn, $_POST['rejection_reason']);
        
        $update_sql = "UPDATE proposals SET 
            status = 'rejected',
            rejection_reason = '$rejection_reason',
            reviewed_by = '{$_SESSION['full_name']}', 
            review_date = NOW() 
            WHERE id = '$proposal_id'";
        $_SESSION['success'] = "Proposal rejected successfully!";
    }
    
    if (isset($update_sql) && mysqli_query($conn, $update_sql)) {
        // Redirect back to the appropriate page based on source
        if ($form_source == 'all') {
            header("Location: all_proposals.php");
        } elseif ($form_source == 'approved') {
            header("Location: approved_proposals.php");
        } elseif ($form_source == 'rejected') {
            header("Location: rejected_proposals.php");
        } else {
            header("Location: pending_reviews.php");
        }
        exit;
    }
}

// Get proposal details from database
$proposal_sql = "SELECT * FROM proposals WHERE id = '$proposal_id'";
$proposal_result = mysqli_query($conn, $proposal_sql);
$proposal = mysqli_fetch_assoc($proposal_result);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found!";
    header("Location: dashboard.php");
    exit;
}

// Determine where to go back based on source
$back_url = "dashboard.php";
$back_text = "Back to Dashboard";

switch($source) {
    case 'pending':
        $back_url = "pending_reviews.php";
        $back_text = "Back to Pending Reviews";
        break;
    case 'approved':
        $back_url = "approved_proposals.php";
        $back_text = "Back to Approved Proposals";
        break;
    case 'rejected':
        $back_url = "rejected_proposals.php";
        $back_text = "Back to Rejected Proposals";
        break;
    case 'all':
        $back_url = "all_proposals.php";
        $back_text = "Back to All Proposals";
        break;
    case 'dashboard':
        $back_url = "dashboard.php";
        $back_text = "Back to Dashboard";
        break;
    default:
        $back_url = "dashboard.php";
        $back_text = "Back to Dashboard";
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Review Proposal</h1>
            <p>Detailed budget review and approval</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo $back_url; ?>" class="btn-back">← <?php echo $back_text; ?></a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="proposal-detail">
        <div class="detail-card">
            <div class="detail-header">
                <h2><?php echo htmlspecialchars($proposal['title']); ?></h2>
                <div class="status-badge status-<?php echo $proposal['status']; ?>">
                    <?php echo ucwords(str_replace('_', ' ', $proposal['status'])); ?>
                </div>
            </div>
            
            <div class="budget-display">
                <span class="budget-amount">₱<?php echo number_format($proposal['proposed_budget'], 2); ?></span>
            </div>
            
            <div class="detail-grid">
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
                    <strong>Submitted:</strong>
                    <span><?php echo date('M j, Y g:i A', strtotime($proposal['date_submitted'])); ?></span>
                </div>
            </div>

            <?php if (!empty($proposal['description'])): ?>
                <div class="detail-section">
                    <h3>Description</h3>
                    <p><?php echo htmlspecialchars($proposal['description']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['budget_breakdown'])): ?>
                <div class="detail-section">
                    <h3>Budget Breakdown</h3>
                    <p><?php echo htmlspecialchars($proposal['budget_breakdown']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['objectives'])): ?>
                <div class="detail-section">
                    <h3>Objectives</h3>
                    <p><?php echo htmlspecialchars($proposal['objectives']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['activities'])): ?>
                <div class="detail-section">
                    <h3>Activities</h3>
                    <p><?php echo htmlspecialchars($proposal['activities']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['expected_outcomes'])): ?>
                <div class="detail-section">
                    <h3>Expected Outcomes</h3>
                    <p><?php echo htmlspecialchars($proposal['expected_outcomes']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($proposal['status'] == 'pending'): ?>
        <div class="action-card">
            <h3>Budget Review & Action</h3>
            
            <!-- Approve Form -->
            <form method="POST" action="" class="action-form">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="source" value="<?php echo $source; ?>">
                
                <div class="form-group">
                    <label for="adjusted_budget">Adjusted Budget Amount:</label>
                    <input type="number" id="adjusted_budget" name="adjusted_budget" 
                           value="<?php echo $proposal['proposed_budget']; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="budget_notes">Budget Notes (Optional):</label>
                    <textarea id="budget_notes" name="budget_notes" 
                              placeholder="Add notes about budget adjustments..." rows="4"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" 
                            onclick="return confirm('Are you sure you want to approve this proposal?')">
                        Approve Proposal
                    </button>
                </div>
            </form>

            <!-- Reject Form -->
            <form method="POST" action="" class="action-form reject-form">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="source" value="<?php echo $source; ?>">
                
                <div class="form-group">
                    <label for="rejection_reason">Reason for Rejection:</label>
                    <textarea id="rejection_reason" name="rejection_reason" 
                              placeholder="Explain why this proposal is being rejected..." 
                              required rows="4"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to reject this proposal?')">
                        Reject Proposal
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="info-card">
                <h3>Review Information</h3>
                <p><strong>Status:</strong> <?php echo ucwords(str_replace('_', ' ', $proposal['status'])); ?></p>
                <p><strong>Reviewed by:</strong> <?php echo $proposal['reviewed_by']; ?></p>
                <p><strong>Review Date:</strong> <?php echo date('M j, Y g:i A', strtotime($proposal['review_date'])); ?></p>
                <?php if ($proposal['status'] == 'approved' && !empty($proposal['budget_notes'])): ?>
                    <p><strong>Budget Notes:</strong> <?php echo $proposal['budget_notes']; ?></p>
                <?php endif; ?>
                <?php if ($proposal['status'] == 'rejected' && !empty($proposal['rejection_reason'])): ?>
                    <p><strong>Rejection Reason:</strong> <?php echo $proposal['rejection_reason']; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>