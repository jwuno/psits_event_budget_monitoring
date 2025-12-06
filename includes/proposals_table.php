<?php
// expects: $proposals (array), $role (string)
?>
<div class="card">
    <h2>Proposals</h2>

    <div class="table-wrapper">
        <table class="proposals-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Event Date</th>
                    <th>Venue</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Stage</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($proposals)): ?>
                <tr>
                    <td colspan="7" class="empty-state">No proposals found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($proposals as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                        <td><?php echo htmlspecialchars($p['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($p['venue']); ?></td>
                        <td>₱<?php echo number_format($p['proposed_budget'], 2); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($p['status'])); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($p['current_stage'])); ?></td>
                        <td>
                            <?php
                            $id = (int)$p['id'];
                            $btnText = 'View';
                            if (in_array($role, ['treasurer','president','adviser']) && $p['status'] === 'pending') {
                                $btnText = 'Review';
                            }
                            ?>
                            <a href="view_proposal.php?id=<?php echo $id; ?>" class="btn btn-sm">
                                <?php echo $btnText; ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
