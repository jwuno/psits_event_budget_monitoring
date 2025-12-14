<?php
// pages/shared/print_proposal.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/db_connect.php';

// Get proposal id
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('Invalid proposal ID.');
}
$proposalId = (int) $_GET['id'];

// Fetch proposal + prepared by
$sql = "
    SELECT p.*,
           u.full_name AS prepared_by
    FROM proposals p
    LEFT JOIN users u
        ON p.created_by = u.username
    WHERE p.id = $proposalId
    LIMIT 1
";

$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) === 0) {
    die('Proposal not found.');
}

$proposal = mysqli_fetch_assoc($res);

// Helper for safe output
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Event duration (new schema: event_start_date / event_end_date; fallback: event_date)
$rawStart = $proposal['event_start_date'] ?? $proposal['event_date'] ?? null;
$rawEnd   = $proposal['event_end_date'] ?? $rawStart;

$eventStart = $rawStart ? date('F d, Y', strtotime($rawStart)) : 'N/A';
$eventEnd   = $rawEnd   ? date('F d, Y', strtotime($rawEnd))   : 'N/A';

// Date submitted
$dateSubmitted = !empty($proposal['date_submitted'])
    ? date('F d, Y', strtotime($proposal['date_submitted']))
    : 'N/A';

// Status text
function formatStatus($s) {
    if ($s === null || $s === '') return 'N/A';
    return ucfirst($s);
}

// Description & budget breakdown (new fields)
$description = trim((string)($proposal['description'] ?? ''));
$budgetBreakdown = trim((string)($proposal['budget_breakdown'] ?? ''));

// Participants: use expected_participants
$participants = $proposal['expected_participants'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Printable Proposal - <?php echo e($proposal['title']); ?></title>
    <style>
        /* ============================
           PRINT LAYOUT
           ============================ */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f3f4f6;
            color: #111827;
            margin: 0;
            padding: 20px;
        }

        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 26px 30px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.18);
        }

        .print-header {
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .print-logo {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .print-heading {
            display: flex;
            flex-direction: column;
        }

        .print-org {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
        }

        .print-title {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .print-subtitle {
            font-size: 0.85rem;
            color: #4b5563;
        }

        .print-meta {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 0.85rem;
            color: #4b5563;
            margin-bottom: 12px;
        }

        .section {
            margin-top: 14px;
        }

        .section-title {
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #374151;
            margin-bottom: 6px;
        }

        .section-body {
            font-size: 0.9rem;
            color: #111827;
            white-space: pre-wrap;
        }

        .info-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.2fr);
            gap: 6px 18px;
            font-size: 0.9rem;
        }

        .info-label {
            font-weight: 500;
            color: #4b5563;
        }

        .info-value {
            color: #111827;
        }

        .status-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            font-size: 0.85rem;
        }

        .status-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px 8px;
        }

        .status-box strong {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .status-value {
            font-weight: 600;
            color: #111827;
        }

        .remarks-text {
            font-size: 0.85rem;
            color: #374151;
            white-space: pre-wrap;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 28px;
            margin-top: 30px;
            font-size: 0.85rem;
        }

        .signature-block {
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #9ca3af;
            margin-bottom: 4px;
            padding-top: 40px;
        }

        .signature-label {
            color: #4b5563;
        }

        /* Screen-only top controls */
        .print-controls {
            max-width: 800px;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-print {
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            padding: 6px 12px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #f3f4f6;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .print-controls {
                display: none;
            }

            .print-container {
                box-shadow: none;
                border-radius: 0;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <button class="btn-print" onclick="window.print()">
        🖨 Print
    </button>
</div>

<div class="print-container">
    <!-- HEADER -->
    <div class="print-header">
        <img src="../../assets/img/psits.png" alt="PSITS Logo" class="print-logo">
        <div class="print-heading">
            <span class="print-org">Philippine Society of Information Technology Students</span>
            <span class="print-title">Event Proposal &amp; Budget Form</span>
            <span class="print-subtitle">BSIT – PSITS Pagadian Annex</span>
        </div>
    </div>

    <!-- META -->
    <div class="print-meta">
        <div>
            <strong>Prepared By:</strong>
            <?php echo e($proposal['prepared_by'] ?: $proposal['created_by']); ?>
        </div>
        <div>
            <strong>Date Submitted:</strong>
            <?php echo e($dateSubmitted); ?>
        </div>
        <div>
            <strong>Proposal ID:</strong>
            #<?php echo e($proposal['id']); ?>
        </div>
    </div>

    <!-- BASIC INFO -->
    <div class="section">
        <div class="section-title">Event Information</div>
        <div class="info-grid">
            <div>
                <span class="info-label">Event Title:</span>
                <span class="info-value"><?php echo e($proposal['title']); ?></span>
            </div>
            <div>
                <span class="info-label">Event Duration:</span>
                <span class="info-value">
                    <?php echo e($eventStart); ?>
                    <?php if ($eventEnd && $eventEnd !== $eventStart): ?>
                        &nbsp;–&nbsp;<?php echo e($eventEnd); ?>
                    <?php endif; ?>
                </span>
            </div>
            <div>
                <span class="info-label">Venue:</span>
                <span class="info-value"><?php echo e($proposal['venue'] ?? ''); ?></span>
            </div>
            <div>
                <span class="info-label">Target Participants:</span>
                <span class="info-value">
                    <?php echo $participants !== '' ? e($participants) : 'N/A'; ?>
                </span>
            </div>
            <div>
                <span class="info-label">Proposed Budget (₱):</span>
                <span class="info-value">
                    ₱<?php echo number_format((float)($proposal['proposed_budget'] ?? 0), 2); ?>
                </span>
            </div>
            <div>
                <span class="info-label">Overall Status:</span>
                <span class="info-value"><?php echo formatStatus($proposal['status'] ?? ''); ?></span>
            </div>
        </div>
    </div>

    <!-- DESCRIPTION / RATIONALE -->
    <?php if ($description !== ''): ?>
        <div class="section">
            <div class="section-title">Event Description / Rationale</div>
            <div class="section-body">
                <?php echo e($description); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- BUDGET BREAKDOWN -->
    <?php if ($budgetBreakdown !== ''): ?>
        <div class="section">
            <div class="section-title">Budget Breakdown</div>
            <div class="section-body">
                <?php echo e($budgetBreakdown); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- STATUS / REMARKS -->
    <div class="section">
        <div class="section-title">Approval Status</div>
        <div class="status-row">
            <div class="status-box">
                <strong>Treasurer</strong>
                <div class="status-value">
                    <?php echo formatStatus($proposal['treasurer_status'] ?? ''); ?>
                </div>
                <?php if (!empty($proposal['treasurer_remarks'])): ?>
                    <div class="remarks-text">
                        <?php echo e($proposal['treasurer_remarks']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="status-box">
                <strong>President</strong>
                <div class="status-value">
                    <?php echo formatStatus($proposal['president_status'] ?? ''); ?>
                </div>
                <?php if (!empty($proposal['president_remarks'])): ?>
                    <div class="remarks-text">
                        <?php echo e($proposal['president_remarks']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="status-box">
                <strong>Adviser</strong>
                <div class="status-value">
                    <?php echo formatStatus($proposal['adviser_status'] ?? ''); ?>
                </div>
                <?php if (!empty($proposal['adviser_remarks'])): ?>
                    <div class="remarks-text">
                        <?php echo e($proposal['adviser_remarks']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SIGNATURE LINES (for printing) -->
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">Secretary</div>
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">Treasurer</div>
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">Adviser</div>
        </div>
    </div>
</div>

<script>
    window.onload = function () {
        window.print();
    };
</script>

</body>
</html>
