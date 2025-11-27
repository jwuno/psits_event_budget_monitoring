<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_connect.php';

// ================== NOTIFICATION HELPERS ==================

function addNotification($conn, $user_role, $message, $created_by = 'System') {
    $user_role  = mysqli_real_escape_string($conn, $user_role);
    $message    = mysqli_real_escape_string($conn, $message);
    $created_by = mysqli_real_escape_string($conn, $created_by);

    $sql = "INSERT INTO notifications (user_role, message, is_read, created_at, created_by)
            VALUES ('$user_role', '$message', 0, NOW(), '$created_by')";
    mysqli_query($conn, $sql);
}

function getUnreadNotifications($conn) {
    if (!isset($_SESSION['role'])) return 0;

    $role = mysqli_real_escape_string($conn, $_SESSION['role']);
    $sql  = "SELECT COUNT(*) AS cnt 
             FROM notifications 
             WHERE user_role = '$role' AND is_read = 0";
    $res  = mysqli_query($conn, $sql);
    $row  = mysqli_fetch_assoc($res);

    return (int)($row['cnt'] ?? 0);
}

function getNotifications($conn, $limit = 50) {
    if (!isset($_SESSION['role'])) return [];

    $role  = mysqli_real_escape_string($conn, $_SESSION['role']);
    $limit = (int)$limit;

    $sql = "SELECT * FROM notifications
            WHERE user_role = '$role'
            ORDER BY created_at DESC
            LIMIT $limit";
    $res = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    return $data;
}

function markNotificationsAsRead($conn) {
    if (!isset($_SESSION['role'])) return;

    $role = mysqli_real_escape_string($conn, $_SESSION['role']);
    $sql  = "UPDATE notifications 
             SET is_read = 1 
             WHERE user_role = '$role' AND is_read = 0";
    mysqli_query($conn, $sql);
}


// Additional utility functions
if (!function_exists('getUserRole')) {
    function getUserRole() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['role'] ?? 'guest';
    }
}

if (!function_exists('getUserName')) {
    function getUserName() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['full_name'] ?? 'Guest';
    }
}

if (!function_exists('getUserId')) {
    function getUserId() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('redirectIfNotLoggedIn')) {
    function redirectIfNotLoggedIn($redirect_url = '../../index.php') {
        if (!isLoggedIn()) {
            header("Location: $redirect_url");
            exit();
        }
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission($required_role) {
        $user_role = getUserRole();
        
        // Define role hierarchy (admin has all permissions)
        $hierarchy = [
            'admin' => ['admin', 'president', 'vice_president', 'secretary', 'treasurer', 'member'],
            'president' => ['president', 'vice_president', 'secretary', 'treasurer', 'member'],
            'vice_president' => ['vice_president', 'secretary', 'treasurer', 'member'],
            'secretary' => ['secretary', 'member'],
            'treasurer' => ['treasurer', 'member'],
            'member' => ['member']
        ];
        
        return in_array($required_role, $hierarchy[$user_role] ?? []);
    }
}

// Database connection helper
if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        static $conn = null;
        
        if ($conn === null) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "psits_event_budget_monitoring";
            
            $conn = new mysqli($servername, $username, $password, $dbname);
            
            if ($conn->connect_error) {
                error_log("Connection failed: " . $conn->connect_error);
                return null;
            }
        }
        
        return $conn;
    }
}

// Security functions
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map('sanitizeInput', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// File upload functions
if (!function_exists('uploadFile')) {
    function uploadFile($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = 2097152) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload error: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            return ['success' => false, 'error' => 'File too large. Maximum size: ' . ($max_size / 1024 / 1024) . 'MB'];
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            return ['success' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types)];
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_path = '../../uploads/' . $filename;
        
        // Create uploads directory if it doesn't exist
        if (!is_dir('../../uploads')) {
            mkdir('../../uploads', 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return ['success' => true, 'filename' => $filename, 'path' => $upload_path];
        } else {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
    }
}

// Date formatting functions
if (!function_exists('formatDate')) {
    function formatDate($date_string, $format = 'F j, Y g:i A') {
        $timestamp = strtotime($date_string);
        return $timestamp ? date($format, $timestamp) : 'Invalid Date';
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M j, Y', $time);
        }
    }
}

// Response formatting
if (!function_exists('jsonResponse')) {
    function jsonResponse($success, $message = '', $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }
}

// Error logging
if (!function_exists('logError')) {
    function logError($message, $file = '', $line = '') {
        $log_message = date('Y-m-d H:i:s') . " - Error: $message";
        if ($file) $log_message .= " in $file";
        if ($line) $log_message .= " on line $line";
        $log_message .= "\n";
        
        // Create logs directory if it doesn't exist
        $log_dir = '../../logs/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        error_log($log_message, 3, $log_dir . 'error.log');
    }
}

?>