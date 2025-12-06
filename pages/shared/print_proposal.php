<?php
/**
 * PROPOSAL SUMMARY REPORT (Printable)
 * -----------------------------------
 * Used by Adviser/President/Any role to print or export PDF.
 */

require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

session_start();

$proposal_id = $_GET['id'] ?? 0;
if (!$proposal_id) {
    die("Invalid proposal ID");
}

// Fetch proposal details
$stmt = $conn->prepare("SELECT * FROM proposals WHERE id = ?");
$stmt->bind_param("i", $proposal_id);
$stmt->execute();
$proposal = $stmt->get_result()->fetch_assoc();

if (!$proposal) {
    die("Proposal not found.");
}

// Format data
function clean($value) {
    return !empty($value) ? nl2br(htmlspecialchars($value)) : '<em>Not provided</em>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Proposal Summary Report - <?php echo htmlspecialchars($proposal['title']); ?></title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #fff;
      color: #222;
      margin: 2rem;
    }

    .report-header {
      text-align: center;
      border-bottom: 3px solid #003366;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .report-header h1 {
      color: #003366;
      margin: 0;
      font-size: 1.8rem;
    }

    .report-meta {
      text-align: center;
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 1.5rem;
    }

    .report-section {
      margin-bottom: 1.5rem;
    }

    .report-section h2 {
      font-size: 1.1rem;
      color: #003366;
      border-left: 4px solid #003366;
      padding-left: 10px;
      margin-bottom: 0.5rem;
    }

    .report-section p {
      font-size: 0.95rem;
      line-height: 1.6;
      text-align: justify;
    }

    .report-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 0.8rem;
      font-size: 0.9rem;
    }

    .report-table th, .report-table td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      text-align: left;
    }

    .report-table th {
      background: #003366;
      color: #fff;
    }

    .remarks-box {
      background: #f7f9fc;
      border: 1px solid #d8dee9;
      border-radius: 6px;
      padding: 10px 12px;
      margin-bottom: 10px;
    }

    .signature-section {
      margin-top: 2.5rem;
      display: flex;
      justify-content: space-around;
    }

    .signature {
      text-align: center;
      margin-top: 40px;
    }

    .signature-line {
      border-top: 1px solid #333;
      width: 200px;
      margin: 0 auto 5px auto;
    }

    .print-btn {
      display: inline-block;
      background: #003366;
      color: #fff;
      padding: 10px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: background 0.3s;
    }

    .print-btn:hover {
      background: #0059b3;
    }

    @media print {
      .no-print {
        display: none;
      }
      body {
        margin: 1rem;
      }
    }
  </style>
</head>
<body>

  <div class="no-print" style="text-align:right;">
    <a href="#" class="print-btn" onclick="window.print()">🖨 Print / Save PDF</a>
  </div>

  <div class="report-header">
    <h1>Proposal Summary Report</h1>
    <p><strong>BSIT Event Budget Monitoring Portal</strong></p>
  </div>

  <div class="report-meta">
    <p><strong>Proposal Title:</strong> <?php echo htmlspecialchars($proposal['title']); ?></p>
    <p><strong>Submitted by:</strong> <?php echo htmlspecialchars($proposal['created_by']); ?> | <strong>Date Submitted:</strong> <?php echo date('F j, Y', strtotime($proposal['date_submitted'])); ?></p>
  </div>

  <div class="report-section">
    <h2>Event Information</h2>
    <table class="report-table">
      <tr><th>Event Date</th><td><?php echo htmlspecialchars($proposal['event_date']); ?></td></tr>
      <tr><th>Venue</th><td><?php echo htmlspecialchars($proposal['venue']); ?></td></tr>
      <tr><th>Expected Participants</th><td><?php echo htmlspecialchars($proposal['expected_participants']); ?></td></tr>
      <tr><th>Proposed Budget</th><td>₱<?php echo number_format($proposal['proposed_budget'], 2); ?></td></tr>
    </table>
  </div>

  <div class="report-section">
    <h2>Description</h2>
    <p><?php echo clean($proposal['description']); ?></p>
  </div>

  <div class="report-section">
    <h2>Objectives</h2>
    <p><?php echo clean($proposal['objectives']); ?></p>
  </div>

  <div class="report-section">
    <h2>Activities</h2>
    <p><?php echo clean($proposal['activities']); ?></p>
  </div>

  <div class="report-section">
    <h2>Expected Outcomes</h2>
    <p><?php echo clean($proposal['expected_outcomes']); ?></p>
  </div>

  <div class="report-section">
    <h2>Budget Breakdown</h2>
    <div class="remarks-box"><?php echo clean($proposal['budget_breakdown']); ?></div>
  </div>

  <div class="report-section">
    <h2>Review Remarks</h2>

    <?php if (!empty($proposal['treasurer_remarks'])): ?>
      <div class="remarks-box"><strong>Treasurer:</strong><br><?php echo clean($proposal['treasurer_remarks']); ?></div>
    <?php endif; ?>

    <?php if (!empty($proposal['president_remarks'])): ?>
      <div class="remarks-box"><strong>President:</strong><br><?php echo clean($proposal['president_remarks']); ?></div>
    <?php endif; ?>

    <?php if (!empty($proposal['adviser_remarks'])): ?>
      <div class="remarks-box"><strong>Adviser:</strong><br><?php echo clean($proposal['adviser_remarks']); ?></div>
    <?php endif; ?>

    <?php if (empty($proposal['treasurer_remarks']) && empty($proposal['president_remarks']) && empty($proposal['adviser_remarks'])): ?>
      <div class="remarks-box"><em>No review remarks recorded yet.</em></div>
    <?php endif; ?>
  </div>

  <div class="report-section">
    <h2>Final Status</h2>
    <p><strong>Status:</strong> <?php echo ucfirst($proposal['status']); ?> | <strong>Stage:</strong> <?php echo ucfirst($proposal['current_stage']); ?></p>
  </div>

  <div class="signature-section">
    <div class="signature">
      <div class="signature-line"></div>
      <p><strong>Treasurer</strong></p>
    </div>
    <div class="signature">
      <div class="signature-line"></div>
      <p><strong>President</strong></p>
    </div>
    <div class="signature">
      <div class="signature-line"></div>
      <p><strong>Adviser</strong></p>
    </div>
  </div>

</body>
</html>
