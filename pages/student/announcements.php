<?php
require_once '../../includes/auth.php';
requireRole('student');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

// Get all announcements with event info
$sql = "
    SELECT a.*, p.title AS event_title, p.event_date, p.venue
    FROM announcements a
    LEFT JOIN proposals p ON a.proposal_id = p.id
    ORDER BY a.created_at DESC
";
$res = mysqli_query($conn, $sql);
?>

<div class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Event Announcements</h1>
            <p>Official PSITS announcements posted by the PIO.</p>
        </div>
    </div>

    <div class="card">
        <?php if (!$res || mysqli_num_rows($res) === 0): ?>
            <p style="margin-top:0.5rem;color:#6b7280;">
                No announcements yet. Please check back later.
            </p>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Announcement</th>
                            <th>Related Event</th>
                            <th>When</th>
                            <th>Where</th>
                            <th>Posted By</th>
                            <th>Posted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td style="white-space:pre-wrap;">
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                    <?php echo htmlspecialchars($row['content']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['event_title'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($row['event_date'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($row['venue'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
