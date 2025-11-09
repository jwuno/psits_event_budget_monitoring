<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Get proposal ID from URL
$proposal_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;

if (!$proposal_id) {
    $_SESSION['error'] = "No proposal specified!";
    header("Location: pending_reviews.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
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
        header("Location: pending_reviews.php");
        exit;
    }
}

// Get proposal details from database
$proposal_sql = "SELECT * FROM proposals WHERE id = '$proposal_id'";
$proposal_result = mysqli_query($conn, $proposal_sql);
$proposal = mysqli_fetch_assoc($proposal_result);

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found!";
    header("Location: pending_reviews.php");
    exit;
}

// Get return URL from parameter or use default
$return_url = isset($_GET['return']) ? $_GET['return'] : 'dashboard.php';

// Validate the return URL to prevent security issues
$allowed_pages = ['dashboard.php', 'pending_reviews.php', 'approved_proposals.php', 'rejected_proposals.php', 'all_proposals.php'];
if (!in_array($return_url, $allowed_pages)) {
    $return_url = 'dashboard.php';
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="header-content">
            <h1>Review Proposal</h1>
            <p>Detailed budget review and approval</p>
        </div>
        <div class="header-actions">
            <?php
            // Get return URL from parameter or use default
            $return_url = isset($_GET['return']) ? $_GET['return'] : 'dashboard.php';
            
            // Validate the return URL to prevent security issues
            $allowed_pages = ['dashboard.php', 'pending_reviews.php', 'approved_proposals.php', 'rejected_proposals.php', 'all_proposals.php'];
            if (!in_array($return_url, $allowed_pages)) {
                $return_url = 'dashboard.php';
            }
            ?>
            <a href="<?php echo $return_url; ?>" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <!-- Rest of your existing content remains the same -->

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="proposal-detail">
        <div class="detail-card">
            <div class="detail-header">
                <h2><?php echo htmlspecialchars($proposal['title']); ?></h2>
                <div class="status-badge status-<?php echo $proposal['status']; ?>">
                    <?php echo ucfirst($proposal['status']); ?>
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
                <p><strong>Status:</strong> <?php echo ucfirst($proposal['status']); ?></p>
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