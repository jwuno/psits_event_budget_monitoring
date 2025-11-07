<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'president') {
    header("Location: ../../index.php");
    exit();
}

if (isset($_POST['proposal_id'])) {
    $proposal_id = $_POST['proposal_id'];
    $remarks = $_POST['remarks'] ?? '';
    
    // Update proposal - using your exact column names
    $sql = "UPDATE proposals SET status = 'approved', president_remarks = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $remarks, $proposal_id);
        
        if ($stmt->execute()) {
            // Add notification
            $message = "Your proposal #$proposal_id has been approved by the President";
            if (!empty($remarks)) {
                $message .= " with remarks: $remarks";
            }
            
            // Get the proposal details
            $creator_sql = "SELECT created_by, title FROM proposals WHERE id = ?";
            $creator_stmt = $conn->prepare($creator_sql);
            $creator_stmt->bind_param("i", $proposal_id);
            $creator_stmt->execute();
            $creator_result = $creator_stmt->get_result();
            
            if ($creator_row = $creator_result->fetch_assoc()) {
                // Include functions for notifications
                include('../../includes/functions.php');
                // Send notification to secretary (the creator)
                addNotification($conn, 'secretary', $message);
                
                // Also send notification to treasurer for budget review
                $treasurer_message = "Proposal approved by President: " . $creator_row['title'] . " - Ready for budget review";
                addNotification($conn, 'treasurer', $treasurer_message);
            }
            
            $_SESSION['success'] = "Proposal approved successfully!";
        } else {
            $_SESSION['error'] = "Error approving proposal: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Database error: " . $conn->error;
    }
} else {
    $_SESSION['error'] = "No proposal ID provided";
}

header("Location: proposals.php");
exit();
?>