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
    
    // File upload handling
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = '../../assets/uploads/';
        $file_name = time() . '_' . basename($_FILES['attachment']['name']);
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $file_name)) {
            $attachment_path = $file_name;
        }
    }
    
    // Insert into database
    $sql = "INSERT INTO proposals (
        title, event_date, venue, expected_participants, proposed_budget, 
        description, objectives, activities, expected_outcomes, budget_breakdown, 
        attachment_path, created_by, status, current_stage
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'under_review', 'treasurer')";   
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssidsssssss", 
            $title, $event_date, $venue, $expected_participants, $proposed_budget,
            $description, $objectives, $activities, $expected_outcomes, $budget_breakdown,
            $attachment_path, $created_by
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $proposal_id = mysqli_insert_id($conn);
            
            // Add notification for treasurer
            include('../../includes/functions.php');
            $message = "New proposal submitted for budget review: " . $title . " by " . $created_by;
            addNotification($conn, 'treasurer', $message);
            
            $_SESSION['success'] = "Proposal submitted successfully! Sent to Treasurer for budget review.";
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