<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users to their dashboards
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Secretary': header("Location: pages/secretary/dashboard.php"); break;
        case 'Treasurer': header("Location: pages/treasurer/dashboard.php"); break;
        case 'President': header("Location: pages/president/dashboard.php"); break;
        case 'Adviser': header("Location: pages/adviser/dashboard.php"); break;
        case 'PIO': header("Location: pages/pio/dashboard.php"); break;
        case 'Student': header("Location: pages/student/dashboard.php"); break;
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSITS Login</title>
    <link rel="stylesheet" href="assets/css/style-login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            
            <!-- Logo -->
            <div class="logo">
                <img src="assets/img/psits.png" alt="PSITS Logo">
            </div>

            <h2>PSITS Login</h2>

            <?php if(isset($_SESSION['error'])): ?>
                <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <form action="validate_login.php" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
