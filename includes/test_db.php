<?php
$conn = new mysqli("localhost", "root", "", "psits_event_budget_monitoring");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!";
}
?>
