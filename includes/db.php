<?php
$servername = "localhost";
$username = "root";
$password = ""; // empty string if no password
$dbname = "psits_event_budget_monitoring";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
