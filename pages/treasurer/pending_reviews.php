<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'treasurer') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// PENDING proposals (fresh from Secretary)
$pending_sql = "
    SELECT *
    FROM proposals
    WHERE status = 'pending'
      AND current_stage = 'treasurer'
    ORDER BY date_submitted DESC
";
$pending_result = mysqli_query($conn, $pending_sql);

// RETURNED BY PRESIDENT (need adjustment by Treasurer)
$returned_sql = "
    SELECT *
    FROM proposals
    WHERE status = 'returned'
      AND current_stage = 'treasurer'
      AND returned_from = 'president'
    ORDER BY review_date DESC, date_submitted DESC
";
$returned_result = mysqli_query($conn, $returned_sql);

include('../../includes/header.php');
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Pending Reviews</h1>
            <p>Proposals awaiting your budget review and adjustments.</p>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- ================== NEW PROPOSALS FROM SECRETARY ================== -->
    <div class="card">
        <div class="card-header">
            <h2>New Proposals from Secretary</h2>
            <p>These proposals are waiting for your initial budget review.</p>
        </div>

        <?php if (mysqli_num_rows($pending_result) === 0): ?>
            <div class="empty-state">
                <p>✅ No new proposals waiting for your initial review.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Created By</th>
                            <th>Submitted</th>
                            <th>Proposed Budget</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($pending_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($p['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($p['created_by']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($p['date_submitted'])); ?></td>
                                <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                                <td>
                                    <a href="review_proposal.php?id=<?php echo $p['id']; ?>" class="btn btn-view">
                                        Review Budget
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================== RETURNED BY PRESIDENT ================== -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h2>Returned by President</h2>
            <p>These proposals were returned by the President for budget adjustments. Update the figures then send them back to the President.</p>
        </div>

        <?php if (mysqli_num_rows($returned_result) === 0): ?>
            <div class="empty-state">
                <p>👍 No proposals currently returned by the President.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Created By</th>
                            <th>Last Reviewed</th>
                            <th>President Remarks</th>
                            <th>Budget</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($returned_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($r['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($r['created_by']); ?></td>
                                <td>
                                    <?php 
                                        echo !empty($r['president_review_date'])
                                            ? date('M j, Y g:i A', strtotime($r['president_review_date']))
                                            : '-';
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        echo !empty($r['president_remarks']) 
                                            ? nl2br(htmlspecialchars($r['president_remarks'])) 
                                            : '<em>No remarks</em>';
                                    ?>
                                </td>
                                <td>₱<?php echo number_format($r['proposed_budget'], 2); ?></td>
                                <td>
                                    <a href="review_proposal.php?id=<?php echo $r['id']; ?>" class="btn btn-primary">
                                        Adjust &amp; Forward
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
