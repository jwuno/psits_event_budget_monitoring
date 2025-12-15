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
            <!-- Scrollable Container for Approved Events -->
            <div class="table-wrapper" style="max-height: 300px; overflow-y: auto;">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Venue</th>
                            <th>Budget</th>
                            <th>Prepared By</th>
                            <th style="width:120px;text-align:center;">Action</th>
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
                                <td style="text-align:center;">
                                    <a href="view_proposal.php?id=<?php echo (int)$row['id']; ?>"
                                       class="btn-light-pill">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================== -->
    <!-- Latest Announcements -->
    <!-- ========================== -->
    <div class="card">
        <h2>Latest Announcements</h2>
        
        <!-- Scrollable Container for Announcements -->
        <div style="max-height: 300px; overflow-y: auto;">
            <?php 
            $annSql = "
                SELECT a.*, p.title AS event_title, p.event_date, p.venue
                FROM announcements a
                LEFT JOIN proposals p ON a.proposal_id = p.id
                ORDER BY a.created_at DESC
                LIMIT 3
            ";
            $annRes = mysqli_query($conn, $annSql);
            
            if (!$annRes || mysqli_num_rows($annRes) === 0): ?>
                <p style="margin-top:0.5rem;color:#6b7280;">
                    No announcements yet. Please check back later.
                </p>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin-top:0.5rem;">
                    <?php while ($a = mysqli_fetch_assoc($annRes)): ?>
                        <li style="margin-bottom:0.75rem;border-bottom:1px solid #e5e7eb;padding-bottom:0.75rem;">
                            <strong><?php echo htmlspecialchars($a['title']); ?></strong><br>
                            <span style="font-size:0.85rem;color:#6b7280;">
                                <?php if (!empty($a['event_title'])): ?>
                                    Related event: <?php echo htmlspecialchars($a['event_title']); ?>
                                    <?php if (!empty($a['event_date'])): ?>
                                        · <?php echo htmlspecialchars($a['event_date']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($a['venue'])): ?>
                                        · <?php echo htmlspecialchars($a['venue']); ?>
                                    <?php endif; ?>
                                    ·
                                <?php endif; ?>
                                Posted by <?php echo htmlspecialchars($a['created_by']); ?>
                                on <?php echo htmlspecialchars($a['created_at']); ?>
                            </span><br>
                            <span style="font-size:0.9rem;white-space:pre-wrap;">
                                <?php echo htmlspecialchars($a['content']); ?>
                            </span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- small pill button style for the View action -->
<style>
.btn-light-pill {
    display:inline-block;
    padding:0.35rem 1.4rem;
    border-radius:999px;
    background-color:#f3f4f6;
    color:#1e293b;
    font-size:0.9rem;
    font-weight:500;
    text-decoration:none;
    border:none;
    transition:background-color 0.15s ease, transform 0.1s ease;
}
.btn-light-pill:hover {
    background-color:#e5e7eb;
    transform:translateY(-1px);
}
</style>

<!-- ========================== -->
<!-- CHART.JS + INLINE CHART CODE -->
<!-- ========================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ----- Data from PHP -----
    const statusData = {
        approved: <?php echo (int)$approvedCount; ?>,
        pending:  <?php echo (int)$pendingCount; ?>,
        rejected: <?php echo (int)$rejectedCount; ?>
    };

    const monthlyLabels = <?php echo json_encode($monthLabels); ?>;
    const monthlyCounts = <?php echo json_encode($monthCounts); ?>;

    // ----- Status doughnut -----
    const statusCanvas = document.getElementById('studentStatusChart');
    if (statusCanvas) {
        new Chart(statusCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [statusData.approved, statusData.pending, statusData.rejected],
                    backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 16
                        }
                    }
                }
            }
        });
    }

    // ----- Monthly bar chart -----
    const activityCanvas = document.getElementById('studentActivityChart');
    if (activityCanvas) {
        new Chart(activityCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Proposals Submitted',
                    data: monthlyCounts,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} proposal(s)`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
