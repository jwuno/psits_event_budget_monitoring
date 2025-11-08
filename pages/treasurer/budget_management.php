<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

include('../../config/db_connect.php');

// Budget statistics
$budget_sql = "SELECT 
    SUM(proposed_budget) as total_requested,
    SUM(CASE WHEN status = 'approved' THEN proposed_budget ELSE 0 END) as total_approved,
    SUM(CASE WHEN status = 'pending' THEN proposed_budget ELSE 0 END) as total_pending,
    COUNT(*) as total_proposals,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count
    FROM proposals";
$budget_result = mysqli_query($conn, $budget_sql);
$budget_stats = mysqli_fetch_assoc($budget_result);

$total_budget_pool = 150000;
$available_budget = $total_budget_pool - $budget_stats['total_approved'];
$utilization_rate = ($budget_stats['total_approved'] / $total_budget_pool) * 100;
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Budget Management</h1>
            <p>Overall budget allocation and utilization</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <div class="budget-overview">
        <div class="budget-card primary">
            <div class="budget-icon">💰</div>
            <div class="budget-info">
                <h3>Total Budget Pool</h3>
                <p class="budget-amount">₱<?php echo number_format($total_budget_pool, 2); ?></p>
            </div>
        </div>
        
        <div class="budget-card success">
            <div class="budget-icon">✅</div>
            <div class="budget-info">
                <h3>Allocated Budget</h3>
                <p class="budget-amount">₱<?php echo number_format($budget_stats['total_approved'], 2); ?></p>
                <p class="budget-desc"><?php echo $budget_stats['approved_count']; ?> approved proposals</p>
            </div>
        </div>
        
        <div class="budget-card warning">
            <div class="budget-icon">⏳</div>
            <div class="budget-info">
                <h3>Pending Budget</h3>
                <p class="budget-amount">₱<?php echo number_format($budget_stats['total_pending'], 2); ?></p>
                <p class="budget-desc">Awaiting approval</p>
            </div>
        </div>
        
        <div class="budget-card info">
            <div class="budget-icon">💸</div>
            <div class="budget-info">
                <h3>Available Budget</h3>
                <p class="budget-amount">₱<?php echo number_format($available_budget, 2); ?></p>
                <p class="budget-desc">Remaining balance</p>
            </div>
        </div>
    </div>

    <div class="utilization-section">
        <div class="utilization-card">
            <h3>Budget Utilization</h3>
            <div class="utilization-bar">
                <div class="utilization-fill" style="width: <?php echo $utilization_rate; ?>%"></div>
            </div>
            <div class="utilization-stats">
                <span><?php echo number_format($utilization_rate, 1); ?>% utilized</span>
                <span>₱<?php echo number_format($available_budget, 2); ?> remaining</span>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>