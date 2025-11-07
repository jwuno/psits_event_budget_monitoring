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
    $expected_participants = mysqli_real_escape_string($conn, $_POST['expected_participants']);
    $proposed_budget = mysqli_real_escape_string($conn, $_POST['proposed_budget']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $objectives = mysqli_real_escape_string($conn, $_POST['objectives']);
    $activities = mysqli_real_escape_string($conn, $_POST['activities']);
    $expected_outcomes = mysqli_real_escape_string($conn, $_POST['expected_outcomes']);
    $budget_breakdown = mysqli_real_escape_string($conn, $_POST['budget_breakdown']);
    $created_by = $_SESSION['full_name'];
    
    // Handle file upload
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../../assets/uploads/";
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check file size (5MB limit)
        if ($_FILES['attachment']['size'] > 5000000) {
            $_SESSION['error'] = "File is too large. Maximum size is 5MB.";
            header("Location: create_proposal.php");
            exit;
        }
        
        // Check file type
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                $attachment_path = $file_name;
            } else {
                $_SESSION['error'] = "Error uploading file.";
                header("Location: create_proposal.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed: PDF, Word, JPG, PNG.";
            header("Location: create_proposal.php");
            exit;
        }
    }
    
    // Insert into database
    $query = "INSERT INTO proposals (
        title, event_date, venue, expected_participants, proposed_budget, 
        description, objectives, activities, expected_outcomes, budget_breakdown,
        created_by, date_submitted, status, attachment_path
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssiisssssss", 
        $title, $event_date, $venue, $expected_participants, $proposed_budget,
        $description, $objectives, $activities, $expected_outcomes, $budget_breakdown,
        $created_by, $attachment_path
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Proposal submitted successfully! Waiting for president approval.";
        header("Location: my_proposals.php");
    } else {
        $_SESSION['error'] = "Error submitting proposal: " . mysqli_error($conn);
        header("Location: create_proposal.php");
    }
    exit;
}
?>