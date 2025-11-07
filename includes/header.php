<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSITS Dashboard</title>
    <!-- Correct relative path for CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="navbar">
  <div class="navbar-left">
    <h2>PSITS System</h2>
  </div>
  <div class="navbar-right">
    <div class="profile-menu">
      <i class="fas fa-user-circle profile-icon" id="profileIcon"></i>
      <div class="dropdown" id="dropdownMenu">
        <p class="user-name">
          <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Guest"; ?>
        </p>
        <hr>
        <a href="../../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>
</header>

<div class="dashboard-banner">
  <h1>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Guest"; ?>!</h1>
  <p>Here's a quick overview of your dashboard.</p>
</div>
