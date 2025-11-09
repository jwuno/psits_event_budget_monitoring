<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pio') {
    header("Location: ../../index.php");
    exit();
}
// ... rest of the edit_announcement.php code remains the same ...

$user_id = $_SESSION['user_id'];
$announcement_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get announcement data
$announcement_query = "SELECT * FROM psits_announcements WHERE id = $announcement_id AND author_id = $user_id";
$announcement_result = mysqli_query($conn, $announcement_query);
$announcement = mysqli_fetch_assoc($announcement_result);

if (!$announcement) {
    header("Location: announcements.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
    
    $update_query = "UPDATE psits_announcements 
                    SET title = '$title', content = '$content', is_urgent = $is_urgent 
                    WHERE id = $announcement_id AND author_id = $user_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = "Announcement updated successfully!";
        header("Location: announcements.php");
        exit();
    } else {
        $error_message = "Error updating announcement: " . mysqli_error($conn);
    }
}
?>

<!-- Reuse the same form style from create_announcement.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement - PIO Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Same styles as create_announcement.php */
        .announcement-form { max-width: 800px; margin: 20px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #2c3e50; }
        .form-group input[type="text"], .form-group textarea { width: 100%; padding: 12px; border: 1px solid #bdc3c7; border-radius: 5px; font-size: 1em; box-sizing: border-box; }
        .form-group textarea { height: 200px; resize: vertical; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        .btn { padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background: #3498db; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-error { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <?php include '../config/includes/header.php'; ?>
    
    <div class="announcement-form">
        <h2>Edit Announcement</h2>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Announcement Title *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($announcement['title']); ?>" required maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="content">Announcement Content *</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($announcement['content']); ?></textarea>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_urgent" name="is_urgent" value="1" <?php echo $announcement['is_urgent'] ? 'checked' : ''; ?>>
                    <label for="is_urgent">Mark as Urgent</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Announcement</button>
                <a href="announcements.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>