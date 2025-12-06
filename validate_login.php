<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_POST['login'])) {
    header("Location: /psits_event_budget_monitoring/index.php");
    exit;
}

$username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
$password = md5($_POST['password'] ?? '');

// Look up user + role
$sql = "SELECT u.*, r.role_name
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.username = '$username'
          AND u.password = '$password'
        LIMIT 1";

$res = mysqli_query($conn, $sql);

if ($res && mysqli_num_rows($res) === 1) {
    $row = mysqli_fetch_assoc($res);

    $_SESSION['user_id']    = $row['user_id'];
    $_SESSION['username']   = $row['username'];
    $_SESSION['full_name']  = $row['full_name'];
    $_SESSION['role']       = strtolower($row['role_name']); // student, secretary, etc.
    $_SESSION['role_label'] = $row['role_name'];              // Student, Secretary, etc.

    $role = $_SESSION['role'];

    // 👇 CHANGE THIS if your folder name is different
    $base = '/psits_event_budget_monitoring/';

    // Absolute redirects (no more relative "pages/...")
    switch ($role) {
        case 'student':
            $target = $base . 'pages/student/dashboard.php';
            break;
        case 'pio':
            $target = $base . 'pages/pio/dashboard.php';
            break;
        case 'secretary':
            $target = $base . 'pages/secretary/dashboard.php';
            break;
        case 'treasurer':
            $target = $base . 'pages/treasurer/dashboard.php';
            break;
        case 'president':
            $target = $base . 'pages/president/dashboard.php';
            break;
        case 'adviser':
            $target = $base . 'pages/adviser/dashboard.php';
            break;
        default:
            $_SESSION['error'] = 'Unknown role for this account.';
            header("Location: " . $base . "index.php");
            exit;
    }

    header("Location: " . $target);
    exit;

} else {
    $_SESSION['error'] = 'Incorrect username or password.';
    header("Location: /psits_event_budget_monitoring/index.php");
    exit;
}
