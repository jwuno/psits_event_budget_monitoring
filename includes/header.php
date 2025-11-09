<?php
require_once __DIR__ . '/../config/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('../../includes/functions.php');

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'guest';

if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Unknown error"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSITS Dashboard</title>
    <link rel="stylesheet" href="/psits_event_budget_monitoring/assets/css/style.css">
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
              $unread_count = getUnreadNotifications($conn);
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
        <div class="profile-icon" id="profileIcon">
          <i class="fas fa-user-circle"></i>
        </div>
        <div class="dropdown" id="dropdownMenu">
          <p class="user-name">
            <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Guest"; ?>
          </p>
          <p class="user-role">
            Role: <?php echo ucfirst($user_role); ?>
          </p>
          <hr>
          <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Logout
          </button>
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

<!-- SIMPLE WORKING JAVASCRIPT -->
<script>
// Simple working functions
console.log("✅ JavaScript loaded!");

const profileIcon = document.getElementById('profileIcon');
const dropdownMenu = document.getElementById('dropdownMenu');
const notificationIcon = document.getElementById('notificationIcon');
const notificationDropdown = document.getElementById('notificationDropdown');

// Initialize - hide dropdowns
if (dropdownMenu) dropdownMenu.style.display = 'none';
if (notificationDropdown) notificationDropdown.style.display = 'none';

// Profile dropdown
if (profileIcon && dropdownMenu) {
    profileIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        console.log("🎯 Profile clicked");
        
        if (dropdownMenu.style.display === 'block') {
            dropdownMenu.style.display = 'none';
        } else {
            dropdownMenu.style.display = 'block';
            // Hide notifications if open
            if (notificationDropdown) notificationDropdown.style.display = 'none';
        }
    });
}

// Notifications dropdown
if (notificationIcon && notificationDropdown) {
    notificationIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        console.log("🔔 Notifications clicked");
        
        if (notificationDropdown.style.display === 'block') {
            notificationDropdown.style.display = 'none';
        } else {
            notificationDropdown.style.display = 'block';
            // Hide profile if open
            if (dropdownMenu) dropdownMenu.style.display = 'none';
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (dropdownMenu && dropdownMenu.style.display === 'block') {
        if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    }
    if (notificationDropdown && notificationDropdown.style.display === 'block') {
        if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.style.display = 'none';
        }
    }
});

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../../logout.php';
    }
}

// Mark all as read
function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
        // Simple implementation - you can add fetch call later
        const badge = document.querySelector('.notification-badge');
        const markReadBtn = document.querySelector('.mark-read-btn');
        if (badge) badge.remove();
        if (markReadBtn) markReadBtn.style.display = 'none';
        alert('Notifications marked as read!');
    }
}

console.log("🎉 All functions ready!");
</script>
</body>
</html>