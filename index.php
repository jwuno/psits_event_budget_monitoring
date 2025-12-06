<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PSITS Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Login-only CSS -->
    <link rel="stylesheet" href="assets/css/style-login.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-body">

<div class="login-wrapper">

    <div class="login-card">
        <div class="login-brand">
            <div class="login-logo">
                <img src="assets/img/psits.png" alt="PSITS Logo">
            </div>
            <div class="login-brand-text">
                <h1 class="login-title">PSITS Event &amp; Budget Portal</h1>
                <p class="login-subtitle">BSIT – PSITS Pagadian Annex</p>
            </div>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="validate_login.php" method="POST" class="login-form">
            <label for="username">Username</label>
            <div class="input-group">
                <span class="input-icon">
                    <i class="fa-regular fa-user"></i>
                </span>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <label for="password">Password</label>
            <div class="input-group">
                <span class="input-icon">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

    </div>

</div>

</body>
</html>
