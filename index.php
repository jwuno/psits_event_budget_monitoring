<?php
// Enable errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session (only here, safe for login page)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSITS Login | JHCSC Pagadian</title>
    <link rel="stylesheet" href="assets/css/style-login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-container">
                <img src="assets/img/psits.png" alt="PSITS Logo" class="logo">
                <h2>PSITS Portal</h2>
                <p>Event Proposal & Budget Monitoring System</p>
                <span class="subtext">JHCSC Pagadian Annex - BSIT</span>
            </div>

            <!-- Error message -->
            <?php if(isset($_SESSION['error'])): ?>
                <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="validate_login.php" method="POST" class="login-form">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your username" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" name="login" class="btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
