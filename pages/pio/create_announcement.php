<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'pio') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
include('../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
    $author_id = $_SESSION['user_id'];
    
    // Check if announcements table exists, if not create it
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'psits_announcements'");
    if (mysqli_num_rows($check_table) == 0) {
        $create_table = "CREATE TABLE psits_announcements (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            author_id INT NOT NULL,
            is_urgent BOOLEAN DEFAULT FALSE,
            is_published BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        )";
        mysqli_query($conn, $create_table);
    }
    
    // Insert the announcement
    $insert_query = "INSERT INTO psits_announcements (title, content, author_id, is_urgent) 
                    VALUES ('$title', '$content', $author_id, $is_urgent)";
    
    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "Announcement created successfully!";
        header("Location: announcements.php");
        exit();
    } else {
        $_SESSION['error'] = "Error creating announcement: " . mysqli_error($conn);
    }
}
?>

<div class="dashboard-container">
    <h2>Create New Announcement</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    
    <div class="form-container" style="max-width: 800px; margin: 20px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="POST" action="">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="title" style="display: block; margin-bottom: 5px; font-weight: bold; color: #2c3e50;">Announcement Title *</label>
                <input type="text" id="title" name="title" required maxlength="255" style="width: 100%; padding: 12px; border: 1px solid #bdc3c7; border-radius: 5px; font-size: 1em; box-sizing: border-box;">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="content" style="display: block; margin-bottom: 5px; font-weight: bold; color: #2c3e50;">Announcement Content *</label>
                <textarea id="content" name="content" required placeholder="Enter the full announcement content here..." style="width: 100%; padding: 12px; border: 1px solid #bdc3c7; border-radius: 5px; font-size: 1em; box-sizing: border-box; height: 200px; resize: vertical;"></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="is_urgent" name="is_urgent" value="1">
                    <label for="is_urgent" style="font-weight: bold; color: #2c3e50;">Mark as Urgent</label>
                </div>
                <small style="color: #7f8c8d; margin-left: 25px;">Urgent announcements will be highlighted in red</small>
            </div>
            
            <div class="form-actions" style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" style="padding: 12px 25px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em;">Publish Announcement</button>
                <a href="dashboard.php" style="padding: 12px 25px; background: #95a5a6; color: white; text-decoration: none; border-radius: 5px; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>