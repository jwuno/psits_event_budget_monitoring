<?php
require_once '../../includes/auth.php';
requireRole('secretary');
include '../../includes/header.php';
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Create New Event Proposal</h1>
        <p>Submit a new event for review by the Treasurer, President, and Adviser.</p>
    </div>

    <div class="form-card">
        <form action="submit_proposal.php" method="post" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Event Title <span style="color:#dc2626">*</span></label>
                    <input type="text" id="title" name="title" required>
                </div>

                <!-- EVENT DURATION -->
                <div class="form-group">
                    <label for="event_start_date">
                        Event Start Date <span style="color:#dc2626">*</span>
                    </label>
                    <input type="date" id="event_start_date" name="event_start_date" required>
                </div>

                <div class="form-group">
                    <label for="event_end_date">Event End Date</label>
                    <input type="date" id="event_end_date" name="event_end_date">
                    <small style="color:#6b7280;font-size:0.8rem;">
                        Leave blank or set the same date if this is a one-day event.
                    </small>
                </div>
                <!-- END EVENT DURATION -->

                <div class="form-group">
                    <label for="venue">Venue <span style="color:#dc2626">*</span></label>
                    <input type="text" id="venue" name="venue" required>
                </div>

                <div class="form-group">
                    <label for="expected_participants">Expected Participants</label>
                    <input type="number" id="expected_participants" name="expected_participants" min="1">
                </div>

                <div class="form-group">
                    <label for="proposed_budget">Proposed Budget (₱) <span style="color:#dc2626">*</span></label>
                    <input type="number" step="0.01" min="0" id="proposed_budget" name="proposed_budget" required>
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="description">Event Description / Rationale</label>
                    <textarea id="description" name="description"></textarea>
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="budget_breakdown">Budget Breakdown</label>
                    <textarea id="budget_breakdown" name="budget_breakdown"
                        placeholder="e.g. Venue – ₱__, Certificates – ₱__, Prizes – ₱__, etc."></textarea>
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="attachment">Attachment (optional)</label>
                    <input type="file" id="attachment" name="attachment"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <small style="color:#6b7280;font-size:0.8rem;">
                        You may upload the detailed proposal, budget sheet, or any supporting document.
                    </small>
                </div>
            </div>

            <div style="margin-top:1rem;display:flex;justify-content:flex-end;gap:0.5rem;">
                <a href="dashboard.php" class="btn btn-sm">Cancel</a>
                <button type="submit" name="submit_proposal" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Submit Proposal
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
