<?php
include('../../includes/db.php');
session_start();

if ($_SESSION['role'] != 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $expected_participants = (int)$_POST['expected_participants'];
    $proposed_budget = floatval($_POST['proposed_budget']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $objectives = mysqli_real_escape_string($conn, $_POST['objectives']);
    $activities = mysqli_real_escape_string($conn, $_POST['activities']);
    $expected_outcomes = mysqli_real_escape_string($conn, $_POST['expected_outcomes']);
    $budget_breakdown = mysqli_real_escape_string($conn, $_POST['budget_breakdown']);
    $created_by = $_SESSION['full_name'];
    
    // Enhanced File Upload Handling
    $attachment_path = null;
    $upload_errors = [];
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = "../../assets/uploads/proposals/";
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['attachment']['name'];
        $file_size = $_FILES['attachment']['size'];
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_error = $_FILES['attachment']['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
        
        // Validate file extension
        if (!in_array($file_ext, $allowed_extensions)) {
            $upload_errors[] = "Invalid file type. Allowed: PDF, Word, JPG, PNG, TXT.";
        }
        
        // Check file size (10MB max)
        $max_size = 10 * 1024 * 1024;
        if ($file_size > $max_size) {
            $upload_errors[] = "File too large. Maximum size is 10MB.";
        }
        
        // Check for upload errors
        if ($file_error !== UPLOAD_ERR_OK) {
            $upload_errors[] = "File upload error code: " . $file_error;
        }
        
        // If no errors, process the file
        if (empty($upload_errors)) {
            // Generate secure filename
            $secure_filename = uniqid() . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
            $target_file = $upload_dir . $secure_filename;
            
            if (move_uploaded_file($file_tmp, $target_file)) {
                $attachment_path = $secure_filename;
                
                // Log successful upload
                error_log("File uploaded successfully: $secure_filename by user: $created_by");
            } else {
                $upload_errors[] = "Failed to move uploaded file.";
            }
        }
        
        // If there were upload errors, show them
        if (!empty($upload_errors)) {
            $_SESSION['error'] = "File upload failed: " . implode(" ", $upload_errors);
            header("Location: create_proposal.php");
            exit;
        }
    }
    
    // Insert into database
    $sql = "INSERT INTO proposals (
        title, event_date, venue, expected_participants, proposed_budget, 
        description, objectives, activities, expected_outcomes, budget_breakdown, 
        attachment_path, created_by, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";   
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssidsssssss", 
            $title, $event_date, $venue, $expected_participants, $proposed_budget,
            $description, $objectives, $activities, $expected_outcomes, $budget_breakdown,
            $attachment_path, $created_by
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $proposal_id = mysqli_insert_id($conn);
            
            // Add notification for president
            include('../../includes/functions.php');
            $message = "New proposal submitted: " . $title . " by " . $created_by;
            addNotification($conn, 'president', $message);
            
            $_SESSION['success'] = "Proposal submitted successfully!" . 
                ($attachment_path ? " File uploaded: " . $_FILES['attachment']['name'] : "");
            header("Location: my_proposals.php");
        } else {
            $_SESSION['error'] = "Error submitting proposal: " . mysqli_error($conn);
            header("Location: create_proposal.php");
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("Location: create_proposal.php");
    }
    exit;
}
?>