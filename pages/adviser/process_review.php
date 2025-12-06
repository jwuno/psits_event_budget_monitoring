<?php
require_once '../../includes/auth.php';
requireRole('adviser', '../../index.php');
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

$id      = (int)($_POST['id'] ?? 0);
$action  = $_POST['action'] ?? '';
$remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');
$by      = mysqli_real_escape_string($conn, $_SESSION['full_name'] ?? 'Adviser');

if ($id <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: dashboard.php");
    exit;
}

if ($action === 'approve') {
    $sql = "UPDATE proposals SET
                adviser_status   = 'approved',
                adviser_remarks  = '$remarks',
                status           = 'approved',
                current_stage    = 'final',
                reviewed_by      = '$by',
                review_date      = NOW()
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        addNotification($conn, 'secretary', "Proposal finally approved by Adviser (ID $id).", $by);
        addNotification($conn, 'pio', "New approved event available (ID $id).", $by);
        $_SESSION['success'] = 'Proposal finally approved.';
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
} else { // final reject
    $sql = "UPDATE proposals SET
                adviser_status   = 'rejected',
                adviser_remarks  = '$remarks',
                status           = 'rejected',
                current_stage    = 'final',
                reviewed_by      = '$by',
                review_date      = NOW()
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        addNotification($conn, 'secretary', "Proposal rejected by Adviser (ID $id).", $by);
        $_SESSION['success'] = 'Proposal finally rejected.';
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
}

header("Location: dashboard.php");
exit;
