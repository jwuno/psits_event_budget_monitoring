<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../login.php");
    exit;
}
include('../../includes/db.php');

$result = mysqli_query($conn, "SELECT * FROM proposals ORDER BY id DESC");
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>All Proposals</h1>
    </div>

    <div class="dashboard-cards">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <table class="card" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Submitted By</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['created_by']; ?></td>
                            <td><?php echo $row['date_submitted']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="card">No proposals found.</p>
        <?php endif; ?>
    </div>
</div>
