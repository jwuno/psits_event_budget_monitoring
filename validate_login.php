<?php
session_start();
include('config/db_connect.php');

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // matches inserted test users

    $query = "SELECT u.*, r.role_name FROM users u 
              JOIN roles r ON u.role_id = r.role_id 
              WHERE u.username='$username' AND u.password='$password'";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['full_name'] = $row['full_name']; // added
        $_SESSION['role'] = $row['role_name'];

        // 🔀 redirect by role
        switch($row['role_name']) {
            case 'student':
                header("Location: pages/student/dashboard.php");
                break;
            case 'pio':
                header("Location: pages/pio/dashboard.php");
                break;
            case 'secretary':
                header("Location: pages/secretary/dashboard.php");
                break;
            case 'treasurer':
                header("Location: pages/treasurer/dashboard.php");
                break;
            case 'president':
                header("Location: pages/president/dashboard.php");
                break;
            case 'adviser':
                header("Location: pages/adviser/dashboard.php");
                break;
            default:
                $_SESSION['error'] = "Invalid role detected!";
                header("Location: login.php");
                break;
        }
        exit;
    } else {
        $_SESSION['error'] = "Incorrect username or password!";
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
