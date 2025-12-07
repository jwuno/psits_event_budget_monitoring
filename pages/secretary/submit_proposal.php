<?php
require_once '../../includes/auth.php';
requireRole('secretary');

require_once '../../config/db_connect.php';

// If not from form, go back
if (!isset($_POST['submit_proposal'])) {
    header('Location: dashboard.php');
    exit;
}

$title       = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
$description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');

// NEW: start/end dates
$event_start_date = mysqli_real_escape_string($conn, $_POST['event_start_date'] ?? '');
$event_end_date   = mysqli_real_escape_string($conn, $_POST['event_end_date'] ?? '');

// We will keep using event_date as the “main” date (for charts, listings, etc.)
$event_date = $event_start_date;

$venue     = mysqli_real_escape_string($conn, $_POST['venue'] ?? '');
$expected  = (int)($_POST['expected_participants'] ?? 0);
$budget    = (float)($_POST['proposed_budget'] ?? 0);
$breakdown = mysqli_real_escape_string($conn, $_POST['budget_breakdown'] ?? '');
$created_by = mysqli_real_escape_string($conn, $_SESSION['username'] ?? '');

// ----- Basic required validation -----
if ($title === '' || $event_start_date === '' || $venue === '' || $budget <= 0) {
    $_SESSION['error'] = 'Please fill out all required fields.';
    header('Location: create_proposal.php');
    exit;
}

// If end date is set, make sure it's not earlier than start date
if ($event_end_date !== '') {
    $startTs = strtotime($event_start_date);
    $endTs   = strtotime($event_end_date);

    if ($endTs === false || $startTs === false || $endTs < $startTs) {
        $_SESSION['error'] = 'Event end date cannot be earlier than the start date.';
        header('Location: create_proposal.php');
        exit;
    }
} else {
    // For one-day events, you may choose to store the same date as end date
    $event_end_date = null; // or set to $event_start_date if you prefer
}

/* ---------- Handle file upload (optional) ---------- */
$attachmentPath = null;

if (!empty($_FILES['attachment']['name'])) {
    $uploadRoot = dirname(__DIR__, 2) . '/uploads'; // projectRoot/uploads
    if (!is_dir($uploadRoot)) {
        mkdir($uploadRoot, 0777, true);
    }

    $fileName   = basename($_FILES['attachment']['name']);
    $ext        = pathinfo($fileName, PATHINFO_EXTENSION);
    $safeName   = uniqid('proposal_', true) . '.' . $ext;
    $targetPath = $uploadRoot . '/' . $safeName;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
        // Save relative path for linking later
        $attachmentPath = 'uploads/' . $safeName;
    } else {
        $_SESSION['error'] = 'File upload failed. Please try again.';
        header('Location: create_proposal.php');
        exit;
    }
}

/* ---------- Insert into proposals ---------- */
$status           = 'pending';
$current_stage    = 'treasurer'; // Treasurer is first reviewer
$treasurer_status = 'pending';
$president_status = 'pending';
$adviser_status   = 'pending';
$returned_from    = null;

$sql = "
    INSERT INTO proposals (
        title,
        description,
        event_date,
        event_start_date,
        event_end_date,
        venue,
        expected_participants,
        proposed_budget,
        budget_breakdown,
        attachment_path,
        created_by,
        status,
        current_stage,
        treasurer_status,
        president_status,
        adviser_status,
        returned_from
    ) VALUES (
        '$title',
        '$description',
        '$event_date',
        '$event_start_date',
        " . ($event_end_date ? "'$event_end_date'" : "NULL") . ",
        '$venue',
        $expected,
        $budget,
        '$breakdown',
        " . ($attachmentPath ? "'" . mysqli_real_escape_string($conn, $attachmentPath) . "'" : "NULL") . ",
        '$created_by',
        '$status',
        '$current_stage',
        '$treasurer_status',
        '$president_status',
        '$adviser_status',
        " . ($returned_from ? "'" . mysqli_real_escape_string($conn, $returned_from) . "'" : "NULL") . "
    )
";

if (mysqli_query($conn, $sql)) {
    $_SESSION['success'] = 'Proposal submitted successfully.';
    header('Location: dashboard.php');
    exit;
} else {
    $_SESSION['error'] = 'Error saving proposal: ' . mysqli_error($conn);
    header('Location: create_proposal.php');
    exit;
}
