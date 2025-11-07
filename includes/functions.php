<?php
// functions.php - PHP Functions Only

// Check if function exists to prevent redeclaration errors
if (!function_exists('getUnreadNotifications')) {
    function getUnreadNotifications($co) {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $user_role = $_SESSION['role'] ?? null;
        
        if (!$user_role) {
            return 0;
        }
        
        // Initialize count
        $count = 0;
        
        // Use user_role instead of user_id - matches your actual table structure
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE is_read = 0 AND user_role = ?";
        $stmt = $co->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $co->error);
            return 0;
        }
        
        $stmt->bind_param("s", $user_role);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return 0;
        }
        
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'] ?? 0;
        }
        
        $stmt->close();
        return $count;
    }
}

if (!function_exists('getNotifications')) {
    function getNotifications($conn, $user_role, $limit = 10) {
        if (!$conn) {
            error_log("Database connection is invalid");
            return [];
        }
        
        $query = "SELECT * FROM notifications WHERE user_role = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "si", $user_role, $limit);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return [];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $notifications = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $notifications;
    }
}

if (!function_exists('markNotificationsAsRead')) {
    function markNotificationsAsRead($conn, $user_role) {
        if (!$conn) {
            error_log("Database connection is invalid");
            return false;
        }
        
        $query = "UPDATE notifications SET is_read = 1 WHERE user_role = ? AND is_read = 0";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $user_role);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Update failed: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
}

if (!function_exists('getAllNotifications')) {
    function getAllNotifications($conn, $user_role, $limit = 50) {
        if (!$conn) {
            error_log("Database connection is invalid");
            return [];
        }
        
        $query = "SELECT * FROM notifications WHERE user_role = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "si", $user_role, $limit);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return [];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $notifications = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $notifications;
    }
}

if (!function_exists('addNotification')) {
    function addNotification($conn, $user_role, $message, $created_by = 'System') {
        if (!$conn) {
            error_log("Database connection is invalid");
            return false;
        }
        
        $query = "INSERT INTO notifications (user_role, message, is_read, created_by, created_at) VALUES (?, ?, 0, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sss", $user_role, $message, $created_by);
        $result = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        return $result;
    }
}

if (!function_exists('getNotificationsForAllRoles')) {
    function getNotificationsForAllRoles($conn, $limit = 10) {
        if (!$conn) {
            error_log("Database connection is invalid");
            return [];
        }
        
        $query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $limit);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return [];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $notifications = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $notifications;
    }
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