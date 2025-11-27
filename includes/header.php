<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/functions.php';

$unread_notifications = getUnreadNotifications($conn);
$role      = $_SESSION['role'] ?? null;
$full_name = $_SESSION['full_name'] ?? 'User';

// Base path per role (adjust if your folder name is different)
$role_base_path = '';
if ($role) {
    $role_base_path = "/psits_event_budget_monitoring/pages/{$role}";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PSITS Event & Budget Portal</title>
    <link rel="stylesheet" href="/psits_event_budget_monitoring/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<!-- TOP NAVBAR -->
<div class="navbar">
    <div class="navbar-left">
        <h2>PSITS Event & Budget Portal</h2>
    </div>

    <?php if ($role): ?>
        <div class="navbar-right">
            <!-- Notification Bell -->
            <div class="notification-menu">
                <a href="<?php echo $role_base_path; ?>/notifications.php" class="notification-icon">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_notifications > 0): ?>
                        <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Profile -->
            <div class="profile-menu">
                <div class="profile-icon" id="profileIcon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="dropdown" id="profileDropdown">
                    <div class="profile-name">
                        <?php echo htmlspecialchars($full_name); ?>
                    </div>
                    <div class="profile-role">
                        <?php echo ucfirst(htmlspecialchars($role)); ?>
                    </div>
                    <hr>
                    <a href="/psits_event_budget_monitoring/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<main class="main-content">
<script>
// Simple dropdown toggle for profile
document.addEventListener('DOMContentLoaded', function() {
    const icon = document.getElementById('profileIcon');
    const dropdown = document.getElementById('profileDropdown');

    if (icon && dropdown) {
        icon.addEventListener('click', function() {
            dropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!icon.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }
});
</script>
