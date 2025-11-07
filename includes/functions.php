<?php
// At the top of functions.php
function getUnreadNotifications($co, $user_role) {
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) FROM notifications WHERE read_status = 0 AND user_id = ? AND user_role = ?";
    $stmt = $co->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['count'] ?? 0; // Use null coalescing operator
}

function getNotifications($conn, $user_role, $limit = 10) {
    if (!$conn) return [];
    
    $query = "SELECT * FROM notifications WHERE user_role = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) return [];
    
    mysqli_stmt_bind_param($stmt, "si", $user_role, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    mysqli_stmt_close($stmt); // Added closing statement
    return $notifications;
}

function markNotificationsAsRead($conn, $user_role) {
    if (!$conn) return false;
    
    $query = "UPDATE notifications SET is_read = TRUE WHERE user_role = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) return false;
    
    mysqli_stmt_bind_param($stmt, "s", $user_role);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt); // Added closing statement
    
    return $result;
}

function getAllNotifications($conn, $user_role, $limit = 50) {
    if (!$conn) return [];
    
    $query = "SELECT * FROM notifications WHERE user_role = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) return [];
    
    mysqli_stmt_bind_param($stmt, "si", $user_role, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    mysqli_stmt_close($stmt); // Added closing statement
    return $notifications;
}
?>