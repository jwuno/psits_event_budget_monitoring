<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');
include('../../includes/functions.php');

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    if (markNotificationsAsRead($conn, $_SESSION['role'])) {
        $_SESSION['success'] = "All notifications marked as read!";
        header("Location: notifications.php");
        exit;
    }
}

// Get all notifications for this role
$notifications = getNotifications($conn, $_SESSION['role'], 50);
$unread_count = getUnreadNotifications($conn);
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Notifications</h1>
        <p>Your notification history</p>
        
        <?php if ($unread_count > 0): ?>
        <div class="notification-actions">
            <a href="?mark_all_read=true" class="btn btn-mark-read" onclick="return confirm('Mark all notifications as read?')">
                <i class="fas fa-check-double"></i> Mark All as Read
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="notifications-container">
        <?php if(!empty($notifications)): ?>
            <div class="notifications-list">
                <?php foreach($notifications as $notification): ?>
                <div class="notification-item-large <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                    <div class="notification-icon-large">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <div class="notification-meta">
                            <span class="notification-sender">By: <?php echo htmlspecialchars($notification['created_by']); ?></span>
                            <span class="notification-time"><?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?></span>
                            <?php if(!$notification['is_read']): ?>
                            <span class="status-badge unread-badge">New</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>No Notifications</h3>
                <p>You don't have any notifications yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="navigation-actions">
        <a href="dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>