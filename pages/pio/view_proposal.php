<?php
require_once '../../includes/auth.php';
requireRole('pio');

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
            <h2>Event Not Found</h2>
            <p>The requested event does not exist or is not accessible.</p>
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

/* --------------------------
   Handle POST: new announcement
   -------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = mysqli_real_escape_string($conn, $_POST['announce_title'] ?? '');
    $content = mysqli_real_escape_string($conn, $_POST['announce_content'] ?? '');
    $creator = mysqli_real_escape_string(
        $conn,
        $_SESSION['full_name'] ?? ($_SESSION['username'] ?? 'PIO')
    );

    if ($title !== '' && $content !== '') {
        $insert = "
            INSERT INTO announcements (proposal_id, title, content, created_by)
            VALUES ($id, '$title', '$content', '$creator')
        ";

        if (mysqli_query($conn, $insert)) {
            $_SESSION['success'] = 'Announcement created for this event.';
            header('Location: view_proposal.php?id=' . $id);
            exit;
        } else {
            $_SESSION['error'] = 'Error creating announcement: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Please provide both a title and announcement content.';
    }
}

// Fetch announcements for this proposal
$annSql = "
    SELECT *
    FROM announcements
    WHERE proposal_id = $id
    ORDER BY created_at DESC
";
$annRes = mysqli_query($conn, $annSql);

$eventSchedule = formatEventSchedule($proposal);
?>

<div class="dashboard">
    <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
        <div>
            <h1>Event Details</h1>
            <p>Use this information when preparing announcements and promotions.</p>
        </div>

        <button type="button" class="btn btn-sm" onclick="window.history.back();">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
    </div>

    <!-- Top summary -->
    <div class="card">
        <div class="stats-grid" style="margin-top:0;">
            <div class="stat-card">
                <span class="stat-label">Overall Status</span>
                <span class="stat-value">
                    <?php echo ucfirst(htmlspecialchars($proposal['status'])); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Event Schedule</span>
                <span class="stat-value">
                    <?php echo htmlspecialchars($eventSchedule); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Venue</span>
                <span class="stat-value">
                    <?php echo htmlspecialchars($proposal['venue']); ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Budget</span>
                <span class="stat-value">
                    ₱<?php echo number_format($proposal['proposed_budget'], 2); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Event information -->
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
                        <th>Description</th>
                        <td style="white-space:pre-wrap;">
                            <?php echo htmlspecialchars($proposal['description']); ?>
                        </td>
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

    <!-- Budget & attachment -->
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

    <!-- Approval trail -->
    <div class="card">
        <h2>Approval Trail</h2>
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

    <!-- Create announcement -->
    <div class="card">
        <h2>Create Announcement for Students</h2>
        <form action="view_proposal.php?id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <label for="announce_title">Announcement Title</label>
                <input type="text" id="announce_title" name="announce_title"
                       value="<?php echo 'Upcoming Event: ' . htmlspecialchars($proposal['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="announce_content">Announcement Content</label>
                <textarea id="announce_content" name="announce_content" rows="4" required
                          placeholder="Example: Join us for [event name] on [date] at [venue]..."></textarea>
            </div>

            <div style="margin-top:1rem;display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-bullhorn"></i> Publish Announcement
                </button>
            </div>
        </form>
    </div>

    <!-- Existing announcements for this event -->
    <div class="card">
        <h2>Announcements Linked to This Event</h2>
        <?php if (!$annRes || mysqli_num_rows($annRes) === 0): ?>
            <p style="margin-top:0.5rem;color:#6b7280;">
                No announcements have been created yet for this event.
            </p>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="proposals-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Posted By</th>
                            <th>Posted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($a = mysqli_fetch_assoc($annRes)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a['title']); ?></td>
                                <td style="white-space:pre-wrap;"><?php echo htmlspecialchars($a['content']); ?></td>
                                <td><?php echo htmlspecialchars($a['created_by']); ?></td>
                                <td><?php echo htmlspecialchars($a['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
