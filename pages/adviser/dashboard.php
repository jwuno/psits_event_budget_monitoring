<?php
include('../../includes/db.php');
include('../../includes/header.php');

// Stats
$events_to_review = $conn->query("SELECT COUNT(*) as total FROM events WHERE status='Pending'")->fetch_assoc()['total'];
$budgets_to_approve = $conn->query("SELECT COUNT(*) as total FROM budgets WHERE status='Pending'")->fetch_assoc()['total'];
$overall_budget = $conn->query("SELECT SUM(amount_requested) as total FROM budgets")->fetch_assoc()['total'];

// Recent pending approvals
$sql_recent = "SELECT * FROM proposals WHERE status='Pending' ORDER BY date_submitted DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
?>

<div class="dashboard-container">

  <div class="dashboard-cards">
    <div class="card">
      <h3>Events to Review</h3>
      <p><?php echo $events_to_review; ?></p>
      <a href="#" class="btn">View</a>
    </div>
    <div class="card">
      <h3>Budgets to Approve</h3>
      <p><?php echo $budgets_to_approve; ?></p>
      <a href="#" class="btn">Approve</a>
    </div>
    <div class="card">
      <h3>Overall Budget</h3>
      <p>₱<?php echo number_format($overall_budget); ?></p>
      <a href="#" class="btn">View Reports</a>
    </div>
  </div>

  <div class="dashboard-table">
    <h3>Pending Approvals</h3>
    <table>
      <thead>
        <tr>
          <th>Event</th>
          <th>Submitted By</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result_recent->num_rows > 0) {
            while($row = $result_recent->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['title']}</td>
                        <td>{$row['created_by']}</td>
                        <td>{$row['status']}</td>
                        <td><a href='#'>Approve/Reject</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No pending approvals.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

</div>

<?php include('../../includes/footer.php'); ?>
