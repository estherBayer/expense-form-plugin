<?php
/**
 * NBNU Form Admin List Template
 * Displays all form submissions in admin
 */
?>

<div class="wrap">
    <h1>NBNU Form Submissions</h1>
    
    <?php if (empty($submissions)): ?>
        <p>No submissions found.</p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Meeting</th>
                    <th>Submission Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): 
                    $form_data = json_decode($submission->form_data, true);
                ?>
                    <tr>
                        <td><?php echo esc_html($submission->id); ?></td>
                        <td><?php echo esc_html($form_data['name'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($form_data['meeting'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html(date('M j, Y g:i A', strtotime($submission->submission_date))); ?></td>
                        <td><?php echo esc_html($form_data['calc_grand_total'] ?? '$0.00'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                                <?php echo esc_html(ucfirst($submission->status)); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=nbnu-form-submissions&view=submission&id=' . $submission->id)); ?>" 
                               class="button button-small">View Details</a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=nbnu-form-submissions&action=export&id=' . $submission->id)); ?>" 
                               class="button button-small">Export PDF</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}
</style>