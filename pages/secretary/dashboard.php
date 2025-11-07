<?php
include('../../includes/db.php');
include('../../includes/header.php');

// Secretary Dashboard Stats
$sql_total = "SELECT COUNT(*) as total FROM proposals WHERE created_by='Secretary'";
$total_proposals = $conn->query($sql_total)->fetch_assoc()['total'];

$sql_pending = "SELECT COUNT(*) as total FROM proposals WHERE status='Pending' AND created_by='Secretary'";
$pending_submissions = $conn->query($sql_pending)->fetch_assoc()['total'];

// Recent submissions
$sql_recent = "SELECT * FROM proposals WHERE created_by='Secretary' ORDER BY date_submitted DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
?>

<div class="dashboard-container">

  <div class="dashboard-cards">
    <div class="card">
      <h3>Total Proposals Created</h3>
      <p><?php echo $total_proposals; ?></p>
      <a href="#" class="btn">View Proposals</a>
    </div>
    <div class="card">
      <h3>Pending Submissions</h3>
      <p><?php echo $pending_submissions; ?></p>
      <a href="#" class="btn">Track Submissions</a>
    </div>
  </div>

  <div class="dashboard-table">
    <h3>Recent Submissions</h3>
    <table>
      <thead>
        <tr>
          <th>Proposal</th>
          <th>Date Submitted</th>
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
                        <td>{$row['date_submitted']}</td>
                        <td>{$row['status']}</td>
                        <td><a href='#'>View</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No proposals yet.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

</div>

<?php include('../../includes/footer.php'); ?>
