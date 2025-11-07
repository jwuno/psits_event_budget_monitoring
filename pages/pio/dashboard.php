<?php
include('../../includes/db.php');
include('../../includes/header.php');

// Stats
$pending = $conn->query("SELECT COUNT(*) as total FROM announcements WHERE status='Pending'")->fetch_assoc()['total'];
$published = $conn->query("SELECT COUNT(*) as total FROM announcements WHERE status='Published'")->fetch_assoc()['total'];
$submissions = $conn->query("SELECT COUNT(*) as total FROM submissions WHERE status='Pending'")->fetch_assoc()['total'];

// Recent submissions
$sql_recent = "SELECT * FROM submissions ORDER BY date_submitted DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
?>

<div class="dashboard-container">

  <div class="dashboard-cards">
    <div class="card">
      <h3>Announcements Pending</h3>
      <p><?php echo $pending; ?></p>
      <a href="#" class="btn">Publish</a>
    </div>
    <div class="card">
      <h3>Published Announcements</h3>
      <p><?php echo $published; ?></p>
      <a href="#" class="btn">View</a>
    </div>
    <div class="card">
      <h3>Submissions to Publish</h3>
      <p><?php echo $submissions; ?></p>
      <a href="#" class="btn">View</a>
    </div>
  </div>

  <div class="dashboard-table">
    <h3>Recent Submissions</h3>
    <table>
      <thead>
        <tr>
          <th>Title</th>
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
                        <td>{$row['submitted_by']}</td>
                        <td>{$row['status']}</td>
                        <td><a href='#'>Publish</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No submissions yet.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

</div>

<?php include('../../includes/footer.php'); ?>
