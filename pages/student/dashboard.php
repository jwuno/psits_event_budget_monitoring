<?php
require_once '../../includes/auth.php';
requireRole('student');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

/* ==========================
   SUMMARY COUNTS / BUDGETS
   ========================== */

// Initialize the counters for statuses
$pendingCount  = 0;
$approvedCount = 0;
$rejectedCount = 0;

// Query to count proposals by status (pending, approved, rejected)
$statusRes = mysqli_query($conn, "
    SELECT status, COUNT(*) AS cnt
    FROM proposals
    GROUP BY status
");
if ($statusRes) {
    while ($row = mysqli_fetch_assoc($statusRes)) {
        switch ($row['status']) {
            case 'pending':
                $pendingCount = (int)$row['cnt'];
                break;
            case 'approved':
                $approvedCount = (int)$row['cnt'];
                break;
            case 'rejected':
                $rejectedCount = (int)$row['cnt'];
                break;
        }
    }
}

// Query to get the total budget by status (approved, pending, rejected)
$approvedBudget = 0;
$pendingBudget  = 0;
$rejectedBudget = 0;

$budgetRes = mysqli_query($conn, "
    SELECT status, COALESCE(SUM(proposed_budget),0) AS total
    FROM proposals
    GROUP BY status
");
if ($budgetRes) {
    while ($row = mysqli_fetch_assoc($budgetRes)) {
        $total = (float)$row['total'];
        switch ($row['status']) {
            case 'approved':
                $approvedBudget = $total;
                break;
            case 'pending':
                $pendingBudget = $total;
                break;
            case 'rejected':
                $rejectedBudget = $total;
                break;
        }
    }
}

/* ==========================
   TOTAL PROPOSALS COUNT
   ========================== */

// Fetch total number of proposals in the system (all proposals regardless of status)
$totalRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM proposals");
$totalRow = mysqli_fetch_assoc($totalRes);
$totalProposals = (int)$totalRow['total'];

/* ==========================
   APPROVED EVENTS TABLE
   ========================== */

// Fetch all approved proposals (those that are fully approved)
$approvedEventsSql = "
    SELECT p.id,
           p.title,
           p.event_date,
           p.venue,
           p.proposed_budget,
           u.full_name AS prepared_by
    FROM proposals p
    LEFT JOIN users u
        ON p.created_by = u.username
    WHERE p.status = 'approved'
    ORDER BY p.date_submitted DESC
";
$approvedEventsRes = mysqli_query($conn, $approvedEventsSql);

/* ==========================
   MONTHLY ACTIVITY (REAL DATA)
   ========================== */

// Create the labels for each month
$monthLabels = [];
$monthCountsMap = [];
for ($m = 1; $m <= 12; $m++) {
    $monthLabels[] = date('M', mktime(0, 0, 0, $m, 1)); // Jan, Feb ...
    $monthCountsMap[$m] = 0;
}

$year = date('Y');

// Query to get the count of proposals submitted each month this year
$monthlyRes = mysqli_query($conn, "
    SELECT MONTH(date_submitted) AS month_num, COUNT(*) AS total
    FROM proposals
    WHERE YEAR(date_submitted) = $year
    GROUP BY MONTH(date_submitted)
    ORDER BY MONTH(date_submitted)
");
if ($monthlyRes) {
    while ($row = mysqli_fetch_assoc($monthlyRes)) {
        $m = (int)$row['month_num'];
        if ($m >= 1 && $m <= 12) {
            $monthCountsMap[$m] = (int)$row['total'];
        }
    }
}

$monthCounts = array_values($monthCountsMap);

/* ==========================
   LATEST ANNOUNCEMENTS
   ========================== */

$annSql = "
    SELECT a.*, p.title AS event_title, p.event_date, p.venue
    FROM announcements a
    LEFT JOIN proposals p ON a.proposal_id = p.id
    ORDER BY a.created_at DESC
";
$annRes = mysqli_query($conn, $annSql);
?>

<div class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Student Transparency Dashboard</h1>
            <p>See how PSITS events and budgets are being approved and used across the organization.</p>
        </div>
    </div>

    <!-- ==========================
         SUMMARY CARDS
         ========================== -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Approved Budget</span>
            <span class="stat-value">₱<?php echo number_format($approvedBudget, 2); ?></span>
            <span class="stat-helper"><?php echo $approvedCount; ?> approved event(s)</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Rejected Budget</span>
            <span class="stat-value">₱<?php echo number_format($rejectedBudget, 2); ?></span>
            <span class="stat-helper"><?php echo $rejectedCount; ?> rejected event(s)</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Total Proposals</span>
            <span class="stat-value">
                <?php echo $totalProposals; ?>
            </span>
            <span class="stat-helper">All PSITS proposals encoded in the system</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Pending Proposals</span>
            <span class="stat-value"><?php echo $pendingCount; ?></span>
            <span class="stat-helper"><?php echo $pendingCount; ?> pending event(s)</span>
        </div>
    </div>

    <!-- ==========================
         CHARTS
         ========================== -->
    <div class="cards-grid-2">
        <!-- Status doughnut -->
        <div class="card">
            <h2>Proposals by Status</h2>
            <p style="color:#6b7280;font-size:0.9rem;margin-bottom:0.5rem;">
                Distribution of proposals across approved, pending, and rejected statuses.
            </p>
            <div class="chart-wrapper" style="height:260px;">
                <canvas id="studentStatusChart"></canvas>
            </div>
        </div>

        <!-- Monthly bar chart -->
        <div class="card">
            <h2>Monthly Proposal Activity (<?php echo $year; ?>)</h2>
            <p style="color:#6b7280;font-size:0.9rem;margin-bottom:0.5rem;">
                Number of proposals submitted each month this year.
            </p>
            <div class="chart-wrapper" style="height:260px;">
                <canvas id="studentActivityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ==========================
         APPROVED EVENTS TABLE
         ========================== -->
    <div class="card">
        <h2>Approved Events & Budgets</h2>
        <p style="color:#6b7280;font-size:0.9rem;margin-bottom:0.5rem;">
            These events have been fully approved (Treasurer, President, and Adviser).
        </p>

        <!-- Scrollable Container for Approved Events -->
        <div class="table-wrapper" style="max-height: 300px; overflow-y: auto;">
            <table class="proposals-table">
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Budget</th>
                        <th>Prepared By</th>
                        <th style="width:120px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$approvedEventsRes || mysqli_num_rows($approvedEventsRes) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#6b7280;">
                                No approved events yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($row = mysqli_fetch_assoc($approvedEventsRes)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                <td>₱<?php echo number_format($row['proposed_budget'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['prepared_by'] ?? ''); ?></td>
                                <td style="text-align:center;">
                                    <a href="view_proposal.php?id=<?php echo (int)$row['id']; ?>"
                                       class="btn-light-pill">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==========================
         LATEST ANNOUNCEMENTS
         ========================== -->
    <div class="card">
        <h2>Latest Announcements</h2>
        <!-- Scrollable Container for Announcements -->
        <div style="max-height: 300px; overflow-y: auto;">
            <?php if (!$annRes || mysqli_num_rows($annRes) === 0): ?>
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

<!-- ==========================
     CHART.JS + INLINE CHART CODE
     ========================== -->
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
