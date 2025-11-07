<?php
// Try this instead of line 3 in header.php:
require_once __DIR__ . '/../config/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include functions for notifications
include('../../includes/functions.php');

// Get current page and user role
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'guest';

// Check database connection
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Unknown error"));
}
?>

<!DOCTYPE html>
<html lang="en">

<style>
/* Emergency fix - hide dropdown initially */
.dropdown {
    display: none !important;
}

/* Make profile icon more visible */
.profile-icon {
    cursor: pointer;
    font-size: 24px;
    color: #333;
    padding: 8px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 2px solid #007bff;
}

.profile-icon:hover {
    background: #e9ecef;
    transform: scale(1.1);
}
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSITS Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="navbar">
  <div class="navbar-left">
    <h2>PSITS System - <?php echo ucfirst($user_role); ?></h2>
  </div>
  <div class="navbar-right">
    <div class="header-actions">
      <!-- Notifications Bell -->
      <div class="notification-menu">
          <div class="notification-icon" id="notificationIcon">
              <i class="fas fa-bell"></i>
              <?php
              // REMOVED the duplicate include('../../includes/db.php'); here
              $unread_count = getUnreadNotifications($conn); // Remove $user_role parameter
              if ($unread_count > 0): ?>
              <span class="notification-badge"><?php echo $unread_count; ?></span>
              <?php endif; ?>
          </div>
          <div class="notification-dropdown" id="notificationDropdown">
              <div class="notification-header">
                  <h4>Notifications</h4>
                  <?php if ($unread_count > 0): ?>
                  <button class="mark-read-btn" onclick="markAllAsRead()">Mark all as read</button>
                  <?php endif; ?>
              </div>
              <div class="notification-list">
                  <?php
                  $notifications = getNotifications($conn, $user_role, 5);
                  if (!empty($notifications)):
                      foreach ($notifications as $notification): ?>
                      <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                          <p><?php echo htmlspecialchars($notification['message']); ?></p>
                          <small><?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?></small>
                      </div>
                      <?php endforeach;
                  else: ?>
                  <div class="notification-item empty">
                      <p>No notifications</p>
                  </div>
                  <?php endif; ?>
              </div>
              <div class="notification-footer">
                <a href="notifications.php">View All Notifications</a>
              </div>
          </div>
      </div>

      <!-- Profile Menu -->
      <div class="profile-menu">
        <i class="fas fa-user-circle profile-icon" id="profileIcon"></i>
        <div class="dropdown" id="dropdownMenu">
          <p class="user-name">
            <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Guest"; ?>
          </p>
          <p class="user-role">
            Role: <?php echo ucfirst($user_role); ?>
          </p>
          <hr>
          <a href="../../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="dashboard-banner">
  <div class="banner-content">
    <h1>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Guest"; ?>!</h1>
    <p>Here's a quick overview of your dashboard.</p>
  </div>
</div>

<!-- Success/Error Messages -->
<div class="messages-container">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Debug Script -->
<script>
console.log("🔍 DEBUG: Checking page load...");

// Check if elements exist
const profileIcon = document.getElementById('profileIcon');
const dropdownMenu = document.getElementById('dropdownMenu');

console.log("Profile Icon:", profileIcon);
console.log("Dropdown Menu:", dropdownMenu);
console.log("Profile Icon exists:", !!profileIcon);
console.log("Dropdown Menu exists:", !!dropdownMenu);

if (profileIcon && dropdownMenu) {
    console.log("✅ Both elements found!");
    
    // Add simple click handler
    profileIcon.addEventListener('click', function(e) {
        console.log("🎯 PROFILE ICON CLICKED!");
        e.stopPropagation();
        
        // Toggle dropdown visibility
        if (dropdownMenu.style.display === 'block') {
            dropdownMenu.style.display = 'none';
            console.log("📁 Dropdown hidden");
        } else {
            dropdownMenu.style.display = 'block';
            console.log("📂 Dropdown shown");
        }
    });
    
    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
            console.log("📭 Click outside - dropdown hidden");
        }
    });
    
} else {
    console.error("❌ Elements missing:", {
        profileIcon: !!profileIcon,
        dropdownMenu: !!dropdownMenu
    });
}

// Force show dropdown for testing
setTimeout(() => {
    console.log("🔄 Testing dropdown...");
    if (dropdownMenu) {
        dropdownMenu.style.display = 'block';
        console.log("🔓 Dropdown forced open for testing");
    }
}, 1000);
</script>

<!-- Your existing script -->
<script src="../../assets/js/script.js"></script>

<!-- IMPORTANT: Include the JavaScript file -->
<script src="../../assets/js/script.js"></script>