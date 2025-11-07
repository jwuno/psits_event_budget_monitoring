<?php
include('../../includes/db.php');
include('../../includes/header.php');

// Treasurer Dashboard Stats
$approved = $conn->query("SELECT COUNT(*) as total FROM budgets WHERE status='Approved'")->fetch_assoc()['total'];
$rejected = $conn->query("SELECT COUNT(*) as total FROM budgets WHERE status='Rejected'")->fetch_assoc()['total'];
$remaining = $conn->query("SELECT SUM(amount_remaining) as total FROM budgets")->fetch_assoc()['total'];

// Recent budgets
$sql_recent = "SELECT * FROM budgets ORDER BY date_requested DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
?>

<div class="dashboard-container">

  <div class="dashboard-cards">
    <div class="card">
      <h3>Approved Budgets</h3>
      <p><?php echo $approved; ?></p>
      <a href="#" class="btn">View</a>
    </div>
    <div class="card">
      <h3>Rejected Budgets</h3>
      <p><?php echo $rejected; ?></p>
      <a href="#" class="btn">View</a>
    </div>
    <div class="card">
      <h3>Remaining Funds</h3>
      <p>₱<?php echo number_format($remaining); ?></p>
      <a href="#" class="btn">Details</a>
    </div>
  </div>

  <div class="dashboard-table">
    <h3>Recent Budgets</h3>
    <table>
      <thead>
        <tr>
          <th>Event</th>
          <th>Requested Amount</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result_recent->num_rows > 0) {
            while($row = $result_recent->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['event_name']}</td>
                        <td>₱{$row['amount_requested']}</td>
                        <td>{$row['status']}</td>
                        <td><a href='#'>View</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No budget entries yet.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

</div>

<?php include('../../includes/footer.php'); ?>
