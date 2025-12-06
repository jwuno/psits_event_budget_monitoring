<?php
require_once '../../includes/auth.php';
requireRole('student');
require_once '../../config/db_connect.php';
include '../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Students should only see APPROVED proposals
$sql = "SELECT * FROM proposals WHERE id = $id AND status = 'approved'";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) === 0) {
    ?>
    <div class="dashboard">
        <div class="card">
            <h2>Proposal Not Available</h2>
            <p>This proposal is either not approved yet or does not exist.</p>
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
            <p>Approved event details and budget for PSITS transparency.</p>
        </div>

        <button type="button" class="btn btn-sm" onclick="window.history.back();">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
    </div>

    <!-- High-level summary -->
    <div class="card">
        <div class="stats-grid" style="margin-top:0;">
            <div class="stat-card approved">
                <span class="stat-label">Final Status</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['status'])); ?>
                </span>
            </div>
            <div class="stat-card budget">
                <span class="stat-label">Approved Budget</span>
                <span class="stat-value">
                    ₱<?php echo number_format($proposal['proposed_budget'], 2); ?>
                </span>
            </div>
        </div>
        <p style="margin-top:0.75rem;color:#6b7280;font-size:0.9rem;">
            This event has completed the internal review of the Treasurer, President, and Adviser
            and is formally approved under PSITS Pagadian Annex.
        </p>
    </div>

    <!-- Event info -->
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
                        <th>Approved Budget</th>
                        <td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Prepared By</th>
                        <td><?php echo htmlspecialchars($proposal['created_by']); ?></td>
                    </tr>
                    <tr>
                        <th>Date Submitted</th>
                        <td><?php echo htmlspecialchars($proposal['date_submitted']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Description, breakdown, attachment -->
    <div class="card">
        <h2>Proposal Details</h2>

        <h3 style="font-size:0.95rem;margin-top:0.2rem;">Event Description / Rationale</h3>
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
</div>

<?php include '../../includes/footer.php'; ?>
