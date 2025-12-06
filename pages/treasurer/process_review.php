<?php
require_once '../../includes/auth.php';
requireRole('treasurer', '../../index.php');
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

$id      = (int)($_POST['id'] ?? 0);
$action  = $_POST['action'] ?? '';
$remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');
$by      = mysqli_real_escape_string($conn, $_SESSION['full_name'] ?? 'Treasurer');

if ($id <= 0 || !in_array($action, ['approve','return'])) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: dashboard.php");
    exit;
}

if ($action === 'approve') {
    $sql = "UPDATE proposals SET
                treasurer_status = 'approved',
                treasurer_remarks = '$remarks',
                status = 'pending',
                current_stage = 'president',
                reviewed_by = '$by',
                review_date = NOW()
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        addNotification($conn, 'president', "Proposal approved by Treasurer: ID $id", $by);
        $_SESSION['success'] = 'Proposal forwarded to President.';
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
} else { // return
    $sql = "UPDATE proposals SET
                treasurer_status = 'rejected',
                treasurer_remarks = '$remarks',
                status = 'returned',
                current_stage = 'secretary',
                returned_from = 'treasurer',
                reviewed_by = '$by',
                review_date = NOW()
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        addNotification($conn, 'secretary', "Proposal returned by Treasurer: ID $id", $by);
        $_SESSION['success'] = 'Proposal returned to Secretary.';
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
}

header("Location: dashboard.php");
exit;
