<?php
require_once __DIR__ . '/../config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function addNotification(mysqli $conn, string $role, string $message, string $by = 'System'): void {
    $role    = strtolower($role);
    $message = mysqli_real_escape_string($conn, $message);
    $by      = mysqli_real_escape_string($conn, $by);

    $sql = "INSERT INTO notifications (user_role, message, created_by)
            VALUES ('$role', '$message', '$by')";
    mysqli_query($conn, $sql);
}

function getUnreadNotifications(mysqli $conn): int {
    if (!isset($_SESSION['role'])) return 0;
    $role = strtolower($_SESSION['role']);
    $sql  = "SELECT COUNT(*) AS cnt FROM notifications
             WHERE user_role = '$role' AND is_read = 0";
    $res  = mysqli_query($conn, $sql);
    $row  = mysqli_fetch_assoc($res);
    return (int)($row['cnt'] ?? 0);
}

function getNotifications(mysqli $conn, int $limit = 10): array {
    if (!isset($_SESSION['role'])) return [];
    $role = strtolower($_SESSION['role']);
    $sql  = "SELECT * FROM notifications
             WHERE user_role = '$role'
             ORDER BY created_at DESC
             LIMIT $limit";
    $res  = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Get proposals list depending on role + filter
 * $filter: 'pending', 'approved', 'rejected', 'all'
 */
function getProposalsForRole(mysqli $conn, string $role, string $filter = 'pending'): array {
    $role   = strtolower($role);
    $where  = [];

    if ($role === 'secretary') {
        $creator = mysqli_real_escape_string($conn, $_SESSION['full_name'] ?? '');
        $where[] = "created_by = '$creator'";
    } else {
        // by stage for reviewers
        if ($filter === 'pending') {
            if ($role === 'treasurer') {
                $where[] = "current_stage = 'treasurer' AND status = 'pending'";
            } elseif ($role === 'president') {
                $where[] = "current_stage = 'president' AND status = 'pending'";
            } elseif ($role === 'adviser') {
                $where[] = "current_stage = 'adviser' AND status = 'pending'";
            }
        }
    }

    if ($filter === 'approved') {
        $where[] = "status = 'approved'";
    } elseif ($filter === 'rejected') {
        $where[] = "status = 'rejected'";
    } elseif ($filter === 'pending') {
        $where[] = "status = 'pending'";
    }

    $whereSql = '';
    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $sql = "SELECT * FROM proposals $whereSql ORDER BY date_submitted DESC";
    $res = mysqli_query($conn, $sql);
    $rows = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}
