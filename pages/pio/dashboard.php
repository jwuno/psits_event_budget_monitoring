<?php
require_once '../../includes/auth.php';
requireRole('pio');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

// Get all FINAL APPROVED proposals
$sql = "
    SELECT *
    FROM proposals
    WHERE status = 'approved'
    ORDER BY event_date ASC, date_submitted DESC
";
$res = mysqli_query($conn, $sql);
?>

<div class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>PIO Dashboard</h1>
            <p>See approved events ready for announcements and promotions.</p>
        </div>
    </div>

    <div class="card">
        <h2>Approved Events</h2>

        <?php if (!$res || mysqli_num_rows($res) === 0): ?>
            <p style="margin-top:0.5rem;color:#6b7280;">
                No approved events yet. Once the Adviser gives final approval, events will appear here.
            </p>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Venue</th>
                            <th>Budget</th>
                            <th>Prepared By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['venue']); ?></td>
                            <td>₱<?php echo number_format($row['proposed_budget'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                            <td>
                                <a href="view_proposal.php?id=<?php echo (int)$row['id']; ?>"
                                   class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>How to Use This Page</h2>
        <p style="color:#6b7280;font-size:0.9rem;line-height:1.5;">
            As the Public Information Officer (PIO), you can view all final-approved PSITS events.
            Use the event details (title, date, venue, description and attachments) as references
            for creating posters, social media posts, and official announcements.
            <br><br>
            This role is <strong>view-only</strong> &mdash; you cannot change proposals or their
            approval status.
        </p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
