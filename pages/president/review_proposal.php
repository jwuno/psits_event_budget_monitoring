<?php
require_once '../../includes/auth.php';
requireRole('president');

require_once '../../config/db_connect.php';
include '../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Fetch proposal
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

/* Event Schedule formatter */
function formatEventSchedule(array $proposal): string
{
    $start  = $proposal['event_start_date'] ?? '';
    $end    = $proposal['event_end_date'] ?? '';
    $single = $proposal['event_date'] ?? '';

    if (empty($start) && !empty($single)) {
        $ts = strtotime($single);
        return $ts ? date('F j, Y', $ts) : $single;
    }

    if (empty($start)) {
        return 'Not set';
    }

    if (empty($end) || $end === $start) {
        $ts = strtotime($start);
        return $ts ? date('F j, Y', $ts) : $start;
    }

    $startTs = strtotime($start);
    $endTs   = strtotime($end);

    if ($startTs === false || $endTs === false) {
        return trim($start . ' - ' . $end);
    }

    if (date('Y', $startTs) === date('Y', $endTs)) {
        if (date('m', $startTs) === date('m', $endTs)) {
            return date('F j', $startTs) . '–' . date('j, Y', $endTs);
        }
        return date('F j', $startTs) . ' – ' . date('F j, Y', $endTs);
    }

    return date('F j, Y', $startTs) . ' – ' . date('F j, Y', $endTs);
}

/* ---------- Handle POST (Approve / Return) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarks = mysqli_real_escape_string($conn, $_POST['president_remarks'] ?? '');
    $now     = date('Y-m-d H:i:s');
    $user    = mysqli_real_escape_string($conn, $_SESSION['username'] ?? 'president');

    if (isset($_POST['action_approve'])) {
        // Approve & forward to Adviser
        $status           = 'pending';
        $current_stage    = 'adviser';
        $president_status = 'approved';

        $update = "
            UPDATE proposals
            SET
                president_status  = '$president_status',
                president_remarks = '$remarks',
                status            = '$status',
                current_stage     = '$current_stage',
                returned_from     = NULL,
                reviewed_by       = '$user',
                review_date       = '$now'
            WHERE id = $id
        ";

        if (mysqli_query($conn, $update)) {
            $_SESSION['success'] = 'Proposal approved and forwarded to the Adviser.';
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error updating proposal: ' . mysqli_error($conn);
        }

    } elseif (isset($_POST['action_return'])) {
        // Return to Treasurer
        $status           = 'returned';
        $current_stage    = 'treasurer';
        $president_status = 'returned';
        $returned_from    = 'president';

        $update = "
            UPDATE proposals
            SET
                president_status  = '$president_status',
                president_remarks = '$remarks',
                status            = '$status',
                current_stage     = '$current_stage',
                returned_from     = '$returned_from',
                reviewed_by       = '$user',
                review_date       = '$now'
            WHERE id = $id
        ";

        if (mysqli_query($conn, $update)) {
            $_SESSION['success'] = 'Proposal returned to the Treasurer with your remarks.';
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error updating proposal: ' . mysqli_error($conn);
        }
    }

    // If error, reload latest
    $res = mysqli_query($conn, "SELECT * FROM proposals WHERE id = $id");
    $proposal = mysqli_fetch_assoc($res);
}

$eventSchedule = formatEventSchedule($proposal);
?>

<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
        <div>
            <h1>President Review</h1>
            <p>Review the event proposal endorsed by the Treasurer and decide whether to forward it to the Adviser.</p>
        </div>

        <button type="button" class="btn btn-sm" onclick="window.history.back();">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
    </div>

    <!-- Status cards -->
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
                <span class="stat-label">Treasurer Status</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['treasurer_status'])); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">President Status</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['president_status'])); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Proposed Budget</span>
                <span class="stat-value">
                    ₱<?php echo number_format($proposal['proposed_budget'], 2); ?>
                </span>
            </div>
        </div>
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
                        <th>Event Schedule</th>
                        <td><?php echo htmlspecialchars($eventSchedule); ?></td>
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

    <!-- Budget + attachment -->
    <div class="card">
        <h2>Budget &amp; Attachments</h2>

        <h3 style="font-size:0.95rem;margin-top:0.2rem;">Budget Breakdown</h3>
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

    <!-- Previous remarks -->
    <div class="card">
        <h2>Previous Remarks</h2>
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

    <!-- President decision form -->
    <div class="card">
        <h2>Your Decision</h2>
        <form action="review_proposal.php?id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <label for="president_remarks">President Remarks / Justification</label>
                <textarea id="president_remarks" name="president_remarks" rows="4"
                          placeholder="Write your decision, conditions, or recommendations."><?php
                    echo htmlspecialchars($proposal['president_remarks']);
                ?></textarea>
            </div>

            <div style="margin-top:1rem;display:flex;justify-content:flex-end;gap:0.5rem;flex-wrap:wrap;">
                <button type="submit" name="action_return" class="btn btn-sm">
                    <i class="fa-solid fa-rotate-left"></i> Return to Treasurer
                </button>
                <button type="submit" name="action_approve" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-check"></i> Approve &amp; Forward to Adviser
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
