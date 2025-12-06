<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
include '../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="dashboard-header">
    <div class="header-content">
      <h1>My Submitted Proposals</h1>
      <p>Proposals you’ve created and submitted for review.</p>
    </div>
    <div class="header-actions">
      <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
  </div>
  <?php include '../../includes/proposals_table.php'; ?>
</div>
<?php include '../../includes/footer.php'; ?>
