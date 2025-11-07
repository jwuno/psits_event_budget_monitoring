<?php
include('../../includes/header.php');
if ($_SESSION['role'] != 'secretary') {
    $_SESSION['error'] = "Access denied!";
    header("Location: ../../index.php");
    exit;
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Create New Proposal</h1>
        <p>Submit a detailed event proposal for approval</p>
    </div>

    <div class="form-container">
        <form action="submit_proposal.php" method="POST" class="proposal-form" enctype="multipart/form-data">
            
            <!-- Basic Information Section -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                
                <div class="form-group">
                    <label for="title">Proposal Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter event title">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" id="event_date" name="event_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="venue">Venue *</label>
                        <input type="text" id="venue" name="venue" required placeholder="Enter event venue">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expected_participants">Expected Participants *</label>
                        <input type="number" id="expected_participants" name="expected_participants" required min="1" placeholder="e.g., 50">
                    </div>
                    
                    <div class="form-group">
                        <label for="proposed_budget">Proposed Budget (₱) *</label>
                        <input type="number" id="proposed_budget" name="proposed_budget" required min="0" step="0.01" placeholder="e.g., 5000.00">
                    </div>
                </div>
            </div>

            <!-- Detailed Information Sections -->
            <div class="form-section">
                <h3><i class="fas fa-file-alt"></i> Proposal Details</h3>
                
                <div class="form-group">
                    <label for="objectives">Objectives *</label>
                    <textarea id="objectives" name="objectives" rows="4" required placeholder="What are the main goals and objectives of this event?"></textarea>
                </div>

                <div class="form-group">
                    <label for="description">Event Description *</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Describe the event purpose, activities, and other relevant details..."></textarea>
                </div>

                <div class="form-group">
                    <label for="activities">Activities & Program *</label>
                    <textarea id="activities" name="activities" rows="4" required placeholder="List the activities, schedule, and program flow..."></textarea>
                </div>

                <div class="form-group">
                    <label for="expected_outcomes">Expected Outcomes *</label>
                    <textarea id="expected_outcomes" name="expected_outcomes" rows="4" required placeholder="What are the expected results and benefits of this event?"></textarea>
                </div>
            </div>

            <!-- Budget Breakdown Section -->
            <div class="form-section">
                <h3><i class="fas fa-money-bill-wave"></i> Budget Breakdown</h3>
                
                <div class="form-group">
                    <label for="budget_breakdown">Detailed Budget Allocation *</label>
                    <textarea id="budget_breakdown" name="budget_breakdown" rows="5" required placeholder="Break down the budget by category:
• Food & Refreshments: ₱______
• Materials & Supplies: ₱______
• Transportation: ₱______
• Venue Rental: ₱______
• Miscellaneous: ₱______
Total: ₱______"></textarea>
                    <small>Please itemize the budget allocation clearly</small>
                </div>
            </div>

            <!-- Attachment Section -->
            <div class="form-section">
                <h3><i class="fas fa-paperclip"></i> Attachments</h3>
                
                <div class="form-group">
                    <label for="attachment">Supporting Documents (Optional)</label>
                    <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small>Supported formats: PDF, Word, JPG, PNG (Max: 5MB)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Proposal
                </button>
                <a href="dashboard.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>