<?php
session_start();
date_default_timezone_set('Asia/Manila'); // set to your actual timezone
require_once 'config/db_connect.php';

if (!isset($_POST['login'])) {
    header("Location: /psits_event_budget_monitoring/index.php");
    exit;
}

$username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
$password = md5($_POST['password'] ?? ''); // matches your existing stored hashes

// Lockout settings
$max_attempts    = 5;
$lockout_seconds = 5 * 60; // 5 minutes

// Fetch user + role
$sql = "SELECT u.*, r.role_name
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.username = '$username'
        LIMIT 1";

$res = mysqli_query($conn, $sql);

if ($res && mysqli_num_rows($res) === 1) {
    $user         = mysqli_fetch_assoc($res);
    $user_id      = (int) $user['user_id'];
    $attempts     = (int) $user['login_attempts'];
    $last_attempt = strtotime($user['last_attempt'] ?? '') ?: 0; // safe fallback
    $now          = time();
    $now_str      = date('Y-m-d H:i:s', $now);

    /* ---------------------------------------
       1) Auto-reset if lockout already expired
    ---------------------------------------- */
    if ($attempts >= $max_attempts && $last_attempt > 0 && ($now - $last_attempt) >= $lockout_seconds) {
        $attempts     = 0;
        $last_attempt = 0;
        $reset_sql = "UPDATE users 
                      SET login_attempts = 0, last_attempt = NULL 
                      WHERE user_id = $user_id";
        mysqli_query($conn, $reset_sql);
    }

    /* -------------------------------
       2) Lockout check (active)
    ------------------------------- */
    if ($attempts >= $max_attempts && $last_attempt > 0 && ($now - $last_attempt) < $lockout_seconds) {
        $remaining = $lockout_seconds - ($now - $last_attempt);
        if ($remaining < 0) $remaining = 0; // safety

        // store remaining seconds for JS countdown
        $_SESSION['lock_remaining'] = $remaining;
        $_SESSION['error'] = 'lockout';

        header("Location: /psits_event_budget_monitoring/index.php");
        exit;
    }

    /* -------------------------------
       3) Check password
    ------------------------------- */
    if ($user['password'] === $password) {
        // ✅ Success – reset attempts
        $reset_sql = "UPDATE users 
                      SET login_attempts = 0, last_attempt = NULL 
                      WHERE user_id = $user_id";
        mysqli_query($conn, $reset_sql);

        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['full_name']  = $user['full_name'];
        $_SESSION['role']       = strtolower($user['role_name']);
        $_SESSION['role_label'] = $user['role_name'];

        $role = $_SESSION['role'];
        $base = '/psits_event_budget_monitoring/';

        switch ($role) {
            case 'student':   $target = $base . 'pages/student/dashboard.php';   break;
            case 'pio':       $target = $base . 'pages/pio/dashboard.php';       break;
            case 'secretary': $target = $base . 'pages/secretary/dashboard.php'; break;
            case 'treasurer': $target = $base . 'pages/treasurer/dashboard.php'; break;
            case 'president': $target = $base . 'pages/president/dashboard.php'; break;
            case 'adviser':   $target = $base . 'pages/adviser/dashboard.php';   break;
            default:
                $_SESSION['error'] = 'Unknown role for this account.';
                header("Location: " . $base . "index.php");
                exit;
        }

        header("Location: " . $target);
        exit;

    } else {
        /* -------------------------------
           4) Wrong password – increment
        ------------------------------- */
        $attempts++;

        $update_sql = "UPDATE users 
                       SET login_attempts = $attempts, last_attempt = '$now_str'
                       WHERE user_id = $user_id";
        mysqli_query($conn, $update_sql);

        if ($attempts >= $max_attempts) {
            // Hit the limit: show lockout with full 5 minutes remaining
            $_SESSION['lock_remaining'] = $lockout_seconds;
            $_SESSION['error'] = 'lockout';
        } else {
            $remaining = $max_attempts - $attempts;
            $_SESSION['error'] = "Incorrect password. {$remaining} attempt(s) remaining.";
        }

        header("Location: /psits_event_budget_monitoring/index.php");
        exit;
    }

} else {
    $_SESSION['error'] = 'Incorrect username or password.';
    header("Location: /psits_event_budget_monitoring/index.php");
    exit;
}
