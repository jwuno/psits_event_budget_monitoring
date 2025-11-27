<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

$notifications = getNotifications($conn, 100);

include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Notifications</h1>
            <p>Updates and actions related to your role.</p>
        </div>
        <div class="header-actions">
            <a href="../../mark_notifications_read.php" class="btn-back">Mark all as read</a>
        </div>
    </div>

    <div class="card">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <p>No notifications yet.</p>
            </div>
        <?php else: ?>
            <ul class="notification-list">
                <?php foreach ($notifications as $notif): ?>
                    <li class="notification-item <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>">
                        <div class="notif-message">
                            <?php echo htmlspecialchars($notif['message']); ?>
                        </div>
                        <div class="notif-meta">
                            <span><?php echo htmlspecialchars($notif['created_by']); ?></span> •
                            <span><?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
