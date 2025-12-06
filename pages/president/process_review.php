<?php
require_once '../../includes/auth.php';
requireRole('president', '../../index.php');
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

$id      = (int)($_POST['id'] ?? 0);
$action  = $_POST['action'] ?? '';
$remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');
$by      = mysqli_real_escape_string($conn, $_SESSION['full_name'] ?? 'President');

if ($id <= 0 || !in_array($action, ['approve', 'return'], true)) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: dashboard.php");
    exit;
}

if ($action === 'approve') {
    $sql = "UPDATE proposals SET
                president_status   = 'approved',
                president_remarks  = '$remarks',
                status             = 'pending',
                current_stage      = 'adviser',
                reviewed_by        = '$by',
                review_date        = NOW()
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        addNotification($conn, 'adviser', "Proposal forwarded by President (ID $id).", $by);
        $_SESSION['success'] = 'Proposal forwarded to Adviser.';
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
} else { // return to Treasurer
    $sql = "UPDATE proposals SET
                president_status   = 'rejected',
                president_remarks  = '$remarks',
                status             = 'returned',
                current_stage      = 'treasurer',
                returned_from      = 'president',
                reviewed_by        = '$by',
                review_date        = NOW()
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        addNotification($conn, 'treasurer', "Proposal returned by President (ID $id).", $by);
        $_SESSION['success'] = 'Proposal returned to Treasurer.';
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
}

header("Location: dashboard.php");
exit;
