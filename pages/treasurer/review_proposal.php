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

$proposal     = mysqli_fetch_assoc($res);
$description  = trim((string)($proposal['description'] ?? ''));

/**
 * Event Schedule (duration) formatter
 * Uses:
 *   - event_start_date + event_end_date  (preferred)
 *   - falls back to event_date if start is empty
 */
function formatEventSchedule(array $proposal): string
{
    $start  = $proposal['event_start_date'] ?? '';
    $end    = $proposal['event_end_date'] ?? '';
    $single = $proposal['event_date'] ?? '';

    // No start date but we have the old single event_date
    if (empty($start) && !empty($single)) {
        $ts = strtotime($single);
        return $ts ? date('F j, Y', $ts) : $single;
    }

    if (empty($start)) {
        return 'Not set';
    }

    // One-day event
    if (empty($end) || $end === $start) {
        $ts = strtotime($start);
        return $ts ? date('F j, Y', $ts) : $start;
    }

    $startTs = strtotime($start);
    $endTs   = strtotime($end);

    if ($startTs === false || $endTs === false) {
        return trim($start . ' - ' . $end);
    }

    // Same year
    if (date('Y', $startTs) === date('Y', $endTs)) {
        // Same month
        if (date('m', $startTs) === date('m', $endTs)) {
            return date('F j', $startTs) . '–' . date('j, Y', $endTs);
        }
        // Different month, same year
        return date('F j', $startTs) . ' – ' . date('F j, Y', $endTs);
    }

    // Different year
    return date('F j, Y', $startTs) . ' – ' . date('F j, Y', $endTs);
}

/**
 * Treasurer can edit certain fields ONLY when:
 *   - it is currently at Treasurer stage
 *   - AND status is "returned"
 *   - AND it was returned by the President
 */
$isEditable =
    $proposal['current_stage'] === 'treasurer' &&
    $proposal['status'] === 'returned' &&
    $proposal['returned_from'] === 'president';

/* ---------- Handle POST (Approve / Return) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarks = mysqli_real_escape_string($conn, $_POST['treasurer_remarks'] ?? '');
    $now     = date('Y-m-d H:i:s');
    $user    = mysqli_real_escape_string($conn, $_SESSION['username'] ?? 'treasurer');

    // Start with existing values
    $newBudget              = (float)$proposal['proposed_budget'];
    $newParticipants        = (int)$proposal['expected_participants'];
    $newBudgetBreakdownRaw  = $proposal['budget_breakdown'];

    if ($isEditable) {
        if (isset($_POST['proposed_budget'])) {
            $newBudget = (float)$_POST['proposed_budget'];
        }
        if (isset($_POST['expected_participants'])) {
            $newParticipants = (int)$_POST['expected_participants'];
        }
        if (isset($_POST['budget_breakdown'])) {
            $newBudgetBreakdownRaw = $_POST['budget_breakdown'];
        }
    }

    $newBudgetBreakdown = mysqli_real_escape_string($conn, $newBudgetBreakdownRaw);

    if (isset($_POST['action_approve'])) {
        // Approve & forward to President
        $status           = 'pending';
        $current_stage    = 'president';
        $treasurer_status = 'approved';

        $update = "
            UPDATE proposals
            SET
                treasurer_status      = '$treasurer_status',
                treasurer_remarks     = '$remarks',
                status                = '$status',
                current_stage         = '$current_stage',
                returned_from         = NULL,
                reviewed_by           = '$user',
                review_date           = '$now',
                proposed_budget       = $newBudget,
                expected_participants = $newParticipants,
                budget_breakdown      = '$newBudgetBreakdown'
            WHERE id = $id
        ";

        if (mysqli_query($conn, $update)) {
            $_SESSION['success'] = 'Proposal updated and forwarded to the President.';
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error updating proposal: ' . mysqli_error($conn);
        }

    } elseif (isset($_POST['action_return'])) {
        // Return to Secretary
        $status           = 'returned';
        $current_stage    = 'secretary';
        $treasurer_status = 'returned';
        $returned_from    = 'treasurer';

        $update = "
            UPDATE proposals
            SET
                treasurer_status      = '$treasurer_status',
                treasurer_remarks     = '$remarks',
                status                = '$status',
                current_stage         = '$current_stage',
                returned_from         = '$returned_from',
                reviewed_by           = '$user',
                review_date           = '$now',
                proposed_budget       = $newBudget,
                expected_participants = $newParticipants,
                budget_breakdown      = '$newBudgetBreakdown'
            WHERE id = $id
        ";

        if (mysqli_query($conn, $update)) {
            $_SESSION['success'] = 'Proposal updated and returned to the Secretary with your remarks.';
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error updating proposal: ' . mysqli_error($conn);
        }
    }

    // If there was an error, reload the latest proposal data
    $res = mysqli_query($conn, "SELECT * FROM proposals WHERE id = $id");
    $proposal    = mysqli_fetch_assoc($res);
    $description = trim((string)($proposal['description'] ?? ''));
    $isEditable =
        $proposal['current_stage'] === 'treasurer' &&
        $proposal['status'] === 'returned' &&
        $proposal['returned_from'] === 'president';
}

$eventSchedule = formatEventSchedule($proposal);
?>

<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
        <div>
            <h1>Treasurer Review</h1>
            <p>Review and decide on the event budget before endorsing it to the President.</p>
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
                <span class="stat-label">Proposed Budget</span>
                <span class="stat-value">
                    ₱<?php echo number_format($proposal['proposed_budget'], 2); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Single form for all editable fields -->
    <form action="review_proposal.php?id=<?php echo $id; ?>" method="post">
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
                            <td>
                                <?php if ($isEditable): ?>
                                    <div style="display: flex; gap: 1rem;">
                                        <input type="date" name="event_start_date" value="<?php echo htmlspecialchars($proposal['event_start_date']); ?>" required>
                                        <input type="date" name="event_end_date" value="<?php echo htmlspecialchars($proposal['event_end_date']); ?>" required>
                                    </div>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($eventSchedule); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Venue</th>
                            <td>
                                <?php if ($isEditable): ?>
                                    <input type="text" name="venue" value="<?php echo htmlspecialchars($proposal['venue']); ?>" required>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($proposal['venue']); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Expected Participants</th>
                            <td>
                                <?php if ($isEditable): ?>
                                    <input
                                        type="number"
                                        id="expected_participants"
                                        name="expected_participants"
                                        min="1"
                                        value="<?php echo (int)$proposal['expected_participants']; ?>"
                                    >
                                    <p style="font-size:0.8rem;color:#6b7280;margin-top:0.25rem;">
                                        Editable because this proposal was returned by the President for adjustment.
                                    </p>
                                <?php else: ?>
                                    <?php echo (int)$proposal['expected_participants']; ?>
                                <?php endif; ?>
                            </td>
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

        <!-- Description -->
        <div class="card">
            <h2>Event Description / Rationale</h2>
            <p style="white-space:pre-wrap;font-size:0.9rem;color:#111827;margin-top:0.5rem;max-height:220px;overflow-y:auto;padding-right:4px;">
                <?php
                echo $description !== ''
                    ? htmlspecialchars($description)
                    : 'No description provided.';
                ?>
            </p>
        </div>

        <!-- Budget + attachment -->
        <div class="card">
            <h2>Budget &amp; Attachments</h2>

            <div class="form-group">
                <label for="proposed_budget">Proposed Budget (₱)</label>
                <?php if ($isEditable): ?>
                    <input
                        type="number"
                        step="0.01"
                        id="proposed_budget"
                        name="proposed_budget"
                        value="<?php echo htmlspecialchars($proposal['proposed_budget']); ?>"
                    >
                <?php else: ?>
                    <div class="readonly-field">
                        ₱<?php echo number_format($proposal['proposed_budget'], 2); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="budget_breakdown">Budget Breakdown</label>
                <?php if ($isEditable): ?>
                    <textarea id="budget_breakdown" name="budget_breakdown" rows="4"><?php
                        echo htmlspecialchars($proposal['budget_breakdown']);
                    ?></textarea>
                <?php else: ?>
                    <p style="white-space:pre-wrap;font-size:0.9rem;color:#111827;margin-top:0.25rem;max-height:220px;overflow-y:auto;padding-right:4px;">
                        <?php
                        echo ($proposal['budget_breakdown'] !== null && $proposal['budget_breakdown'] !== '')
                            ? htmlspecialchars($proposal['budget_breakdown'])
                            : 'No budget breakdown provided.';
                        ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($proposal['attachment_path'])): ?>
                <div class="form-group">
                    <label>Attachment</label>
                    <p>
                        <a class="btn btn-sm btn-primary"
                           href="<?php echo '../../' . htmlspecialchars($proposal['attachment_path']); ?>"
                           target="_blank">
                            <i class="fa-solid fa-file-arrow-down"></i> View / Download Attachment
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Previous higher-level remarks -->
        <div class="card">
            <h2>President Remarks</h2>
            <p style="white-space:pre-wrap;margin-top:0.25rem;">
                <?php
                echo ($proposal['president_remarks'] !== null && $proposal['president_remarks'] !== '')
                    ? htmlspecialchars($proposal['president_remarks'])
                    : 'None.';
                ?>
            </p>
        </div>

        <!-- Treasurer remarks + actions -->
        <div class="card">
            <h2>Your Decision</h2>
            <div class="form-group">
                <label for="treasurer_remarks">Treasurer Remarks / Justification</label>
                <textarea id="treasurer_remarks" name="treasurer_remarks" rows="4"
                          placeholder="Write your comments, adjustments made, or basis for approval."><?php
                    echo htmlspecialchars($proposal['treasurer_remarks']);
                ?></textarea>
            </div>

            <div style="margin-top:1rem;display:flex;justify-content:flex-end;gap:0.5rem;flex-wrap:wrap;">
                <button type="submit" name="action_return" class="btn btn-sm">
                    <i class="fa-solid fa-rotate-left"></i> Return to Secretary
                </button>
                <button type="submit" name="action_approve" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-check"></i> Approve &amp; Forward to President
                </button>
            </div>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
