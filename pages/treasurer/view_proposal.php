<?php
require_once '../../includes/auth.php';
requireRole('treasurer');
require_once '../../config/db_connect.php';
include '../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$sql = "SELECT * FROM proposals WHERE id = $id";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) === 0) {
    ?>
    <div class="dashboard">
        <div class="card">
            <h2>Proposal Not Found</h2>
            <p>The requested proposal does not exist.</p>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.history.back();">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

$proposal = mysqli_fetch_assoc($res);
?>

<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
        <div>
            <h1><?php echo htmlspecialchars($proposal['title']); ?></h1>
            <p>Event proposal details and approval status.</p>
        </div>

        <button type="button" class="btn btn-sm" onclick="window.history.back();">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
    </div>

    <div class="card">
        <div class="stats-grid" style="margin-top:0;">
            <div class="stat-card">
                <span class="stat-label">Overall Status</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['status'])); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Current Stage</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['current_stage'])); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Treasurer</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['treasurer_status'])); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">President</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['president_status'])); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Adviser</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['adviser_status'])); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Event Information</h2>
        <div class="table-wrapper">
            <table class="proposals-table">
                <tbody>
                    <tr>
                        <th style="width:220px;">Event Title</th>
                        <td><?php echo htmlspecialchars($proposal['title']); ?></td>
                    </tr>
                    <tr>
                        <th>Event Date</th>
                        <td><?php echo htmlspecialchars($proposal['event_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Venue</th>
                        <td><?php echo htmlspecialchars($proposal['venue']); ?></td>
                    </tr>
                    <tr>
                        <th>Expected Participants</th>
                        <td><?php echo htmlspecialchars($proposal['expected_participants']); ?></td>
                    </tr>
                    <tr>
                        <th>Proposed Budget</th>
                        <td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Submitted By</th>
                        <td><?php echo htmlspecialchars($proposal['created_by']); ?></td>
                    </tr>
                    <tr>
                        <th>Date Submitted</th>
                        <td><?php echo htmlspecialchars($proposal['date_submitted']); ?></td>
                    </tr>
                    <tr>
                        <th>Returned From</th>
                        <td><?php echo $proposal['returned_from'] ? htmlspecialchars($proposal['returned_from']) : '—'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Proposal Details</h2>

        <h3 style="font-size:0.95rem;margin-top:0.2rem;">Description / Rationale</h3>
        <p style="white-space:pre-wrap;margin-top:0.25rem;">
            <?php
            echo ($proposal['description'] !== null && $proposal['description'] !== '')
                ? htmlspecialchars($proposal['description'])
                : 'No description provided.';
            ?>
        </p>

        <h3 style="font-size:0.95rem;margin-top:1rem;">Budget Breakdown</h3>
        <p style="white-space:pre-wrap;margin-top:0.25rem;">
            <?php
            echo ($proposal['budget_breakdown'] !== null && $proposal['budget_breakdown'] !== '')
                ? htmlspecialchars($proposal['budget_breakdown'])
                : 'No budget breakdown provided.';
            ?>
        </p>

        <?php if (!empty($proposal['attachment_path'])): ?>
            <h3 style="font-size:0.95rem;margin-top:1rem;">Attachment</h3>
            <p style="margin-top:0.25rem;">
                <a class="btn btn-sm btn-primary"
                   href="<?php echo '../../' . htmlspecialchars($proposal['attachment_path']); ?>"
                   target="_blank">
                    <i class="fa-solid fa-file-arrow-down"></i> View / Download Attachment
                </a>
            </p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Remarks</h2>
        <div class="table-wrapper">
            <table class="proposals-table">
                <tbody>
                    <tr>
                        <th style="width:220px;">Treasurer Remarks</th>
                        <td>
                            <?php
                            echo ($proposal['treasurer_remarks'] !== null && $proposal['treasurer_remarks'] !== '')
                                ? nl2br(htmlspecialchars($proposal['treasurer_remarks']))
                                : 'None.';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>President Remarks</th>
                        <td>
                            <?php
                            echo ($proposal['president_remarks'] !== null && $proposal['president_remarks'] !== '')
                                ? nl2br(htmlspecialchars($proposal['president_remarks']))
                                : 'None.';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Adviser Remarks</th>
                        <td>
                            <?php
                            echo ($proposal['adviser_remarks'] !== null && $proposal['adviser_remarks'] !== '')
                                ? nl2br(htmlspecialchars($proposal['adviser_remarks']))
                                : 'None.';
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
