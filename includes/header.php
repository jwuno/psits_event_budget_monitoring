<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/helpers.php';


// 👇 same base as in auth.php
$base = '/psits_event_budget_monitoring/';

$fullName  = $_SESSION['full_name']  ?? '';
$roleLabel = $_SESSION['role_label'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PSITS Event &amp; Budget Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet"
          href="<?php echo $base; ?>assets/css/style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<header class="main-header">
    <div class="header-left">
        <div class="logo-wrap">
            <img src="<?php echo $base; ?>assets/img/psits.png"
                 alt="PSITS Logo"
                 class="logo-img">
        </div>
        <div class="header-title-group">
            <h1>PSITS Event &amp; Budget Monitoring Portal</h1>
            <p>BSIT – PSITS Pagadian Annex</p>
        </div>
    </div>

    <div class="header-right">
        <div class="user-menu">
            <span class="user-name">
                <?php echo htmlspecialchars($fullName); ?>
            </span>
            <span class="user-role">
                <?php echo htmlspecialchars($roleLabel); ?>
            </span>
            <a href="<?php echo $base; ?>logout.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
</header>

<main class="main-content">
