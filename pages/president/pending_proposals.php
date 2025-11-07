<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'president') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../login.php");
    exit;
}
include('../../includes/db.php');

// Approve / Reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'approve') {
        mysqli_query($conn, "UPDATE proposals SET status='president_approved' WHERE id=$id");
    } elseif ($_GET['action'] == 'reject') {
        mysqli_query($conn, "UPDATE proposals SET status='rejected' WHERE id=$id");
    }
    header("Location: pending_proposals.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM proposals WHERE status='budget_approved'");
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Pending Proposals</h1>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['created_by']; ?></td>
                            <td><?php echo $row['date_submitted']; ?></td>
                            <td>
                                <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn">Approve</a>
                                <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn" style="background:#e74c3c;">Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="card">No pending proposals found.</p>
        <?php endif; ?>
    </div>
</div>
