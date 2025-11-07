<?php
include('../../includes/db.php');
include('../../includes/header.php');

// Stats
$total = $conn->query("SELECT COUNT(*) as total FROM proposals")->fetch_assoc()['total'];
$approved = $conn->query("SELECT COUNT(*) as total FROM proposals WHERE status='Approved'")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) as total FROM proposals WHERE status='Pending'")->fetch_assoc()['total'];

// Recent proposals
$sql_recent = "SELECT * FROM proposals ORDER BY date_submitted DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
?>

<div class="dashboard-container">

  <div class="dashboard-cards">
    <div class="card">
      <h3>Total Budgets</h3>
      <p><?php echo $total; ?></p>
      <a href="#" class="btn">View</a>
    </div>
    <div class="card">
      <h3>Approved Proposals</h3>
      <p><?php echo $approved; ?></p>
      <a href="#" class="btn">View</a>
    </div>
    <div class="card">
      <h3>Pending Proposals</h3>
      <p><?php echo $pending; ?></p>
      <a href="#" class="btn">View</a>
    </div>
  </div>

  <div class="dashboard-table">
    <h3>Recent Proposals Overview</h3>
    <table>
      <thead>
        <tr>
          <th>Proposal</th>
          <th>Status</th>
          <th>Budget</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result_recent->num_rows > 0) {
            while($row = $result_recent->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['title']}</td>
                        <td>{$row['status']}</td>
                        <td>₱{$row['amount_requested']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No proposals yet.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

</div>

<?php include('../../includes/footer.php'); ?>
