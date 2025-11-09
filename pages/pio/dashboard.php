<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'pio') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');

// Count active events
$active_events_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE status='approved' AND event_date >= CURDATE()");
$active_events = $active_events_result ? mysqli_fetch_assoc($active_events_result)['total'] : 0;

// Count upcoming events (next 7 days)
$upcoming_events_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE status='approved' AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$upcoming_events = $upcoming_events_result ? mysqli_fetch_assoc($upcoming_events_result)['total'] : 0;

// Check if announcements table exists and count announcements
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'psits_announcements'");
$announcements_table_exists = mysqli_num_rows($table_check) > 0;

if ($announcements_table_exists) {
    $my_announcements_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM psits_announcements WHERE author_id = '{$_SESSION['user_id']}'");
    $my_announcements = $my_announcements_result ? mysqli_fetch_assoc($my_announcements_result)['total'] : 0;
    
    // Get recent announcements
    $recent_announcements_result = mysqli_query($conn, "SELECT * FROM psits_announcements WHERE author_id = '{$_SESSION['user_id']}' ORDER BY created_at DESC LIMIT 5");
} else {
    $my_announcements = 0;
    $recent_announcements_result = false;
}
?>

<div class="dashboard-container">
    <h2>Public Information Officer Dashboard</h2>
    <p>Manage announcements and promote events for PSITS JHCSC</p>
    
    <div class="dashboard-cards">
        <div class="card">
            <h3>Active Events</h3>
            <p><?php echo $active_events; ?></p>
            <a href="#" class="btn">View Events</a>
        </div>
        <div class="card">
            <h3>Upcoming Events</h3>
            <p><?php echo $upcoming_events; ?></p>
            <a href="#" class="btn">View Upcoming</a>
        </div>
        <div class="card">
            <h3>My Announcements</h3>
            <p><?php echo $my_announcements; ?></p>
            <a href="announcements.php" class="btn">View All</a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions" style="margin: 30px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <a href="create_announcement.php" class="btn" style="padding: 15px; text-align: center; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">
            📢 Create New Announcement
        </a>
        <a href="manage_promotions.php" class="btn" style="padding: 15px; text-align: center; background: #2ecc71; color: white; text-decoration: none; border-radius: 5px;">
            🎯 Manage Event Promotions
        </a>
        <a href="announcements.php" class="btn" style="padding: 15px; text-align: center; background: #9b59b6; color: white; text-decoration: none; border-radius: 5px;">
            📋 View All Announcements
        </a>
    </div>

    <!-- Recent Announcements -->
    <div class="recent-section" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="color: #2c3e50; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #3498db;">My Recent Announcements</h3>
        
        <?php if($recent_announcements_result && mysqli_num_rows($recent_announcements_result) > 0): ?>
            <?php while($announcement = mysqli_fetch_assoc($recent_announcements_result)): ?>
                <div class="announcement-item" style="padding: 15px; border-bottom: 1px solid #ecf0f1; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-weight: bold; color: #2c3e50;">
                            <?php echo htmlspecialchars($announcement['title']); ?>
                            <?php if($announcement['is_urgent']): ?>
                                <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; margin-left: 10px;">URGENT</span>
                            <?php endif; ?>
                        </span>
                        <div style="color: #7f8c8d; font-size: 0.9em;">
                            <?php echo date('M j, Y g:i A', strtotime($announcement['created_at'])); ?>
                        </div>
                    </div>
                    <div>
                        <a href="edit_announcement.php?id=<?php echo $announcement['id']; ?>" class="btn" style="padding: 8px 15px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-size: 0.9em;">Edit</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 30px; color: #7f8c8d;">
                <p>No announcements yet. <a href="create_announcement.php">Create your first announcement!</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>