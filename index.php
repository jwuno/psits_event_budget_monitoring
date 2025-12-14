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

            <!-- Error / lockout message -->
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error" style="color: red; font-weight: bold; text-align: center; margin-top: 10px;">
                    <?php if (isset($_SESSION['lock_remaining'])): ?>
                        Too many failed attempts. Try again in
                        <span id="lock-timer"
                              data-remaining="<?= (int) $_SESSION['lock_remaining']; ?>"></span>.
                    <?php else: ?>
                        <?= htmlspecialchars($_SESSION['error']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

    </div>

</div>

<?php
// We unset AFTER rendering so JS still sees the value
unset($_SESSION['error'], $_SESSION['lock_remaining']);
?>

<!-- Simple JS countdown -->
<script>
(function () {
    const el = document.getElementById('lock-timer');
    if (!el) return;

    let remaining = parseInt(el.dataset.remaining, 10);
    if (isNaN(remaining)) return;

    function updateTimer() {
        if (remaining < 0) remaining = 0;

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;

        el.textContent = minutes + "m " + String(seconds).padStart(2, '0') + "s";

        if (remaining > 0) {
            remaining--;
            setTimeout(updateTimer, 1000);
        }
    }

    updateTimer();
})();
</script>

</body>
</html>
