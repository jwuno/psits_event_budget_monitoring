<?php
include('../../includes/db.php');
session_start();

if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

if (isset($_POST['proposal_id'])) {
    $proposal_id = $_POST['proposal_id'];
    
    // Update proposal status to approved
    $query = "UPDATE proposals SET status = 'Approved', updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $proposal_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Proposal approved successfully!";
        
        // Get proposal details for notification
        $proposal_query = "SELECT * FROM proposals WHERE id = ?";
        $proposal_stmt = mysqli_prepare($conn, $proposal_query);
        mysqli_stmt_bind_param($proposal_stmt, "i", $proposal_id);
        mysqli_stmt_execute($proposal_stmt);
        $proposal_result = mysqli_stmt_get_result($proposal_stmt);
        $proposal = mysqli_fetch_assoc($proposal_result);
        
        // Create notification for secretary
        $notification_message = "Your proposal '".$proposal['title']."' has been APPROVED by the president.";
        $notification_query = "INSERT INTO notifications (user_role, message, created_by, created_at) VALUES ('secretary', ?, 'System', NOW())";
        $notification_stmt = mysqli_prepare($conn, $notification_query);
        mysqli_stmt_bind_param($notification_stmt, "s", $notification_message);
        mysqli_stmt_execute($notification_stmt);
        
    } else {
        $_SESSION['error'] = "Error approving proposal: " . mysqli_error($conn);
    }
    
    header("Location: pending_proposals.php");
    exit;
}
?>