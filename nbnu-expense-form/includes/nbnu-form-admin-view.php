<?php
/**
 * NBNU Form Admin Detail View Template
 * Displays individual form submission details
 */
?>

<div class="wrap">
    <h1>NBNU Form Submission #<?php echo esc_html($submission->id); ?></h1>
    
    <div class="submission-meta">
        <p><strong>Submitted:</strong> <?php echo esc_html(date('F j, Y g:i A', strtotime($submission->submission_date))); ?></p>
        <p><strong>Status:</strong> 
            <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                <?php echo esc_html(ucfirst($submission->status)); ?>
            </span>
        </p>
    </div>

    <div class="submission-details">
        <h2>Personal Information</h2>
        <table class="form-table">
            <tr>
                <th>Name:</th>
                <td><?php echo esc_html($form_data['name'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Meeting:</th>
                <td><?php echo esc_html($form_data['meeting'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Dates:</th>
                <td><?php echo esc_html($form_data['dates'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Address:</th>
                <td><?php echo esc_html($form_data['address'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Employer:</th>
                <td><?php echo esc_html($form_data['employer'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Classification:</th>
                <td><?php echo esc_html($form_data['classification'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Hourly Rate:</th>
                <td><?php echo esc_html($form_data['hourly_rate'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Out of Province:</th>
                <td><?php echo esc_html($form_data['out_of_province'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Date of Birth:</th>
                <td><?php echo esc_html(($form_data['dob_month'] ?? '') . '/' . ($form_data['dob_day'] ?? '') . '/' . ($form_data['dob_year'] ?? '')); ?></td>
            </tr>
            <?php if (!empty($form_data['local_office_email'])): ?>
            <tr>
                <th>Local Office Email:</th>
                <td><?php echo esc_html($form_data['local_office_email']); ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <h2>Daily Breakdown</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Date</th>
                    <th>Travel Hours</th>
                    <th>Meeting Hours</th>
                    <th>Employer Billing</th>
                    <th>Day Off</th>
                    <th>LTD/WHSCC</th>
                    <th>KMs</th>
                    <th>Meals</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $days_full = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $days_short = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
                
                foreach ($days_short as $index => $day): 
                    if (!empty($form_data[$day]['date'])):
                ?>
                    <tr>
                        <td><?php echo esc_html($days_full[$index]); ?></td>
                        <td><?php echo esc_html($form_data[$day]['date'] ?? ''); ?></td>
                        <td><?php echo esc_html($form_data[$day]['hours_travel'] ?? '0'); ?></td>
                        <td><?php echo esc_html($form_data[$day]['hours_meeting'] ?? '0'); ?></td>
                        <td><?php echo esc_html($form_data[$day]['employer_billing'] ?? 'No'); ?></td>
                        <td><?php echo esc_html($form_data[$day]['day_off'] ?? 'Yes'); ?></td>
                        <td><?php echo esc_html($form_data[$day]['ltd_whscc'] ?? 'Yes'); ?></td>
                        <td>
                            <?php 
                            $kms = $form_data[$day]['kms'] ?? $form_data[$day]['kms_manual'] ?? '0';
                            $round_trip = ($form_data[$day]['round_trip'] ?? 'no') === 'yes' ? ' (Round Trip)' : '';
                            echo esc_html($kms . $round_trip);
                            ?>
                        </td>
                        <td>
                            <?php
                            $meals = [];
                            if (($form_data[$day]['meal_breakfast'] ?? 'no') === 'yes') $meals[] = 'B';
                            if (($form_data[$day]['meal_lunch'] ?? 'no') === 'yes') $meals[] = 'L';
                            if (($form_data[$day]['meal_supper'] ?? 'no') === 'yes') $meals[] = 'S';
                            echo esc_html(implode(', ', $meals));
                            ?>
                        </td>
                    </tr>
                <?php 
                    endif;
                endforeach; 
                ?>
            </tbody>
        </table>

        <h2>Travel Information</h2>
        <table class="form-table">
            <tr>
                <th>Used Own Car:</th>
                <td><?php echo esc_html($form_data['use_own_car'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Travelled From:</th>
                <td><?php echo esc_html($form_data['travelled_from'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Travelled To:</th>
                <td><?php echo esc_html($form_data['travelled_to'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Manual KM Entry:</th>
                <td><?php echo esc_html($form_data['manual_km_entry'] ?? 'no'); ?></td>
            </tr>
        </table>

        <h2>Accommodation & Expenses</h2>
        <table class="form-table">
            <tr>
                <th>Hotel Nights:</th>
                <td><?php echo esc_html($form_data['hotel_nights'] ?? '0'); ?></td>
            </tr>
            <tr>
                <th>Hotel Rate per Night:</th>
                <td><?php echo esc_html($form_data['hotel_rate'] ?? '$0.00'); ?></td>
            </tr>
            <tr>
                <th>Private Accommodation Nights:</th>
                <td><?php echo esc_html($form_data['private_nights'] ?? '0'); ?></td>
            </tr>
            <tr>
                <th>Other Expenses:</th>
                <td><?php echo esc_html($form_data['other_expenses'] ?? '$0.00'); ?></td>
            </tr>
        </table>

        <h2>Calculated Totals</h2>
        <table class="form-table">
            <tr>
                <th>Total Hours (Travel + Meeting):</th>
                <td><?php echo esc_html($form_data['calc_total_hours'] ?? '0.00'); ?></td>
            </tr>
            <tr>
                <th>Hours Billed by Employer:</th>
                <td><?php echo esc_html($form_data['calc_billed_hours'] ?? '0.00'); ?></td>
            </tr>
            <tr>
                <th>Hours Paid:</th>
                <td><?php echo esc_html($form_data['calc_paid_hours'] ?? '0.00'); ?></td>
            </tr>
            <tr>
                <th>Final Hours Payment:</th>
                <td><strong><?php echo esc_html($form_data['calc_final_pay'] ?? '$0.00'); ?></strong></td>
            </tr>
            <tr>
                <th>Mileage Total:</th>
                <td><strong><?php echo esc_html($form_data['calc_mileage'] ?? '$0.00'); ?></strong></td>
            </tr>
            <tr>
                <th>Meals Total:</th>
                <td><strong><?php echo esc_html($form_data['calc_meals'] ?? '$0.00'); ?></strong></td>
            </tr>
            <tr>
                <th>Hotel Total:</th>
                <td><strong><?php echo esc_html($form_data['calc_hotel'] ?? '$0.00'); ?></strong></td>
            </tr>
            <tr>
                <th>Private Accommodation Total:</th>
                <td><strong><?php echo esc_html($form_data['calc_private'] ?? '$0.00'); ?></strong></td>
            </tr>
            <tr>
                <th>Other Expenses Total:</th>
                <td><strong><?php echo esc_html($form_data['calc_other'] ?? '$0.00'); ?></strong></td>
            </tr>
            <tr style="border-top: 2px solid #ddd;">
                <th style="font-size: 1.2em;">GRAND TOTAL:</th>
                <td style="font-size: 1.2em;"><strong><?php echo esc_html($form_data['calc_grand_total'] ?? '$0.00'); ?></strong></td>
            </tr>
        </table>

        <?php if (!empty($form_data['comments'])): ?>
        <h2>Comments</h2>
        <div class="comments-box">
            <?php echo nl2br(esc_html($form_data['comments'])); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($form_data['uploaded_files'])): ?>
        <h2>Uploaded Files</h2>
        <ul>
            <?php foreach ($form_data['uploaded_files'] as $file_url): ?>
                <li><a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_html(basename($file_url)); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <div class="submission-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=nbnu-form-submissions')); ?>" class="button">← Back to All Submissions</a>
        <button type="button" class="button button-primary" onclick="window.print()">Print</button>
        <a href="<?php echo esc_url(admin_url('admin.php?page=nbnu-form-submissions&action=export&id=' . $submission->id)); ?>" class="button">Export PDF</a>
    </div>
</div>

<style>
.submission-meta {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.submission-details h2 {
    margin-top: 30px;
    margin-bottom: 15px;
    color: #2F7F9D;
    border-bottom: 2px solid #f1f1f1;
    padding-bottom: 5px;
}

.submission-details table {
    margin-bottom: 20px;
}

.comments-box {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #2F7F9D;
}

.submission-actions {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 5px;
}

.submission-actions .button {
    margin-right: 10px;
}

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

@media print {
    .submission-actions, .wp-admin, #wpadminbar {
        display: none !important;
    }
    
    .wrap {
        margin: 0 !important;
    }
}
</style>