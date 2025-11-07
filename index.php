<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSITS Event & Budget Monitoring | JHCSC Pagadian Annex</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-container">
                <img src="assets/img/psits_logo.png" alt="PSITS Logo" class="logo">
                <h2>PSITS Portal</h2>
                <p>Event Proposal & Budget Monitoring System</p>
                <span class="subtext">JHCSC Pagadian Annex - BSIT</span>
            </div>

            <form action="validate_login.php" method="POST" class="login-form">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your username" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
