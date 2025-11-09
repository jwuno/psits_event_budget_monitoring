<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pio') {
    header("Location: ../../index.php");
    exit();
}
// ... rest of the announcements.php code remains the same ...

$user_id = $_SESSION['user_id'];

// Handle announcement deletion
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_query = "DELETE FROM psits_announcements WHERE id = $delete_id AND author_id = $user_id";
    mysqli_query($conn, $delete_query);
    $_SESSION['success_message'] = "Announcement deleted successfully!";
    header("Location: announcements.php");
    exit();
}

// Get all announcements by this PIO
$announcements_query = "SELECT * FROM psits_announcements WHERE author_id = $user_id ORDER BY created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_query);

// Check if table exists, if not show empty state
$check_table = "SHOW TABLES LIKE 'psits_announcements'";
$table_exists = mysqli_query($conn, $check_table);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Announcements - PIO Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .announcements-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .announcement-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .announcement-card.urgent {
            border-left-color: #e74c3c;
            background: #fff5f5;
        }
        
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .announcement-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        
        .announcement-meta {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .announcement-content {
            line-height: 1.6;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .announcement-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9em;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .btn-new {
            background: #27ae60;
            color: white;
            padding: 12px 20px;
        }
        
        .urgent-badge {
            background: #e74c3c;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <?php include '../config/includes/header.php'; ?>
    
    <div class="announcements-container">
        <div class="page-header">
            <h2>My Announcements</h2>
            <a href="create_announcement.php" class="btn btn-new">+ New Announcement</a>
        </div>
        
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($table_exists) == 0 || mysqli_num_rows($announcements_result) == 0): ?>
            <div class="empty-state">
                <h3>No Announcements Yet</h3>
                <p>You haven't created any announcements. Create your first announcement to get started!</p>
                <a href="create_announcement.php" class="btn btn-new" style="margin-top: 20px;">Create First Announcement</a>
            </div>
        <?php else: ?>
            <?php while($announcement = mysqli_fetch_assoc($announcements_result)): ?>
                <div class="announcement-card <?php echo $announcement['is_urgent'] ? 'urgent' : ''; ?>">
                    <div class="announcement-header">
                        <h3 class="announcement-title">
                            <?php echo htmlspecialchars($announcement['title']); ?>
                            <?php if($announcement['is_urgent']): ?>
                                <span class="urgent-badge">URGENT</span>
                            <?php endif; ?>
                        </h3>
                        <div class="announcement-meta">
                            <?php echo date('F j, Y g:i A', strtotime($announcement['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="announcement-content">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>
                    
                    <div class="announcement-actions">
                        <a href="edit_announcement.php?id=<?php echo $announcement['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="announcements.php?delete_id=<?php echo $announcement['id']; ?>" class="btn btn-delete" 
                           onclick="return confirm('Are you sure you want to delete this announcement?')">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html>