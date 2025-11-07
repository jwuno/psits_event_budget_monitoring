<?php
session_start();
include('includes/db.php');
include('includes/functions.php');

if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false]);
    exit;
}

if (markNotificationsAsRead($conn, $_SESSION['role'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>