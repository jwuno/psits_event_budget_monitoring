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
    
    if (empty($remarks)) {
        $_SESSION['error'] = "Please provide remarks for rejection";
        header("Location: proposals.php");
        exit();
    }
    
    // Update proposal - using your exact column names
    $sql = "UPDATE proposals SET status = 'rejected', president_remarks = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $remarks, $proposal_id);
        
        if ($stmt->execute()) {
            // Add notification
            $message = "Your proposal #$proposal_id has been rejected by the President. Remarks: $remarks";
            
            // Get the proposal details
            $creator_sql = "SELECT created_by, title FROM proposals WHERE id = ?";
            $creator_stmt = $conn->prepare($creator_sql);
            $creator_stmt->bind_param("i", $proposal_id);
            $creator_stmt->execute();
            $creator_result = $creator_stmt->get_result();
            
            if ($creator_row = $creator_result->fetch_assoc()) {
                // Include functions for notifications
                include('../../includes/functions.php');
                addNotification($conn, 'secretary', $message);
            }
            
            $_SESSION['success'] = "Proposal rejected successfully!";
        } else {
            $_SESSION['error'] = "Error rejecting proposal: " . $stmt->error;
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