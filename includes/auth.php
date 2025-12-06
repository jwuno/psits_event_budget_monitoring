<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 👇 CHANGE if your folder name is different
$base = '/psits_event_budget_monitoring/';

require_once __DIR__ . '/../config/db_connect.php';

/**
 * Ensure user is logged in
 */
function requireLogin(string $redirect = null): void
{
    global $base;

    if (!isset($_SESSION['user_id'])) {
        $target = $redirect ?: $base . 'index.php';
        header("Location: $target");
        exit;
    }
}

/**
 * Ensure user has specific role
 */
function requireRole(string $role, string $redirect = null): void
{
    global $base;

    requireLogin($redirect);

    $currentRole = strtolower($_SESSION['role'] ?? '');

    if ($currentRole !== strtolower($role)) {
        // Not allowed → just send them to login
        $target = $redirect ?: $base . 'index.php';
        header("Location: $target");
        exit;
    }
}
