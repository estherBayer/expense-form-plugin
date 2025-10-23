<?php
/**
 * NBNU Form Admin Detail View Template
 * Displays individual form submission details
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$day_keys = [ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ];
$day_names = [
    'sun' => __( 'Sunday', 'nbnu-expense-form' ),
    'mon' => __( 'Monday', 'nbnu-expense-form' ),
    'tue' => __( 'Tuesday', 'nbnu-expense-form' ),
    'wed' => __( 'Wednesday', 'nbnu-expense-form' ),
    'thu' => __( 'Thursday', 'nbnu-expense-form' ),
    'fri' => __( 'Friday', 'nbnu-expense-form' ),
    'sat' => __( 'Saturday', 'nbnu-expense-form' ),
];

$submission_date = ! empty( $submission['submission_date'] )
    ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission['submission_date'] ) )
    : '';
?>

<div class="wrap">
    <h1><?php printf( esc_html__( 'NBNU Form Submission #%d', 'nbnu-expense-form' ), (int) $submission['id'] ); ?></h1>

    <div class="submission-meta">
        <p><strong><?php esc_html_e( 'Submitted:', 'nbnu-expense-form' ); ?></strong> <?php echo esc_html( $submission_date ); ?></p>
        <p><strong><?php esc_html_e( 'Status:', 'nbnu-expense-form' ); ?></strong>
            <span class="status-badge status-<?php echo esc_attr( $submission['status'] ); ?>">
                <?php echo esc_html( nbnu_expense_get_status_label( $submission['status'] ) ); ?>
            </span>
        </p>
    </div>

    <div class="submission-details">
        <h2><?php esc_html_e( 'Personal Information', 'nbnu-expense-form' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Name:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_name'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Meeting:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_meeting'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Dates:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_dates'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Address:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_address'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Employer:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_employer'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Classification:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_classifications'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Hourly Rate:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_hourly_rate'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Out of Province:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_meeting_out_of_province'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Date of Birth:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( implode( '/', array_filter( [ $form_data['date_month'] ?? '', $form_data['date_day'] ?? '', $form_data['date_year'] ?? '' ] ) ) ); ?></td>
            </tr>
            <?php if ( ! empty( $form_data['form_provincial_or_local_office_email'] ) ) : ?>
            <tr>
                <th><?php esc_html_e( 'Local Office Email:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_provincial_or_local_office_email'] ); ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <h2><?php esc_html_e( 'Daily Breakdown', 'nbnu-expense-form' ); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Day', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Travel Hours', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Meeting Hours', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Employer Billing', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Day Off', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'LTD/WHSCC', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'KMs', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Meals', 'nbnu-expense-form' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $day_keys as $day_key ) :
                    $date_value = $form_data[ 'form_' . $day_key . '_date' ] ?? '';
                    if ( '' === $date_value ) {
                        continue;
                    }

                    $travel_hours = $form_data[ 'form_' . $day_key . '_hours_travel' ] ?? '0';
                    $meeting_hours = $form_data[ 'form_' . $day_key . '_hours_meeting' ] ?? '0';
                    $billing = $form_data[ 'form_' . $day_key . '_employer_billing_NBNU' ] ?? __( 'No', 'nbnu-expense-form' );
                    $day_off = $form_data[ 'form_' . $day_key . '_day_off' ] ?? '';
                    $ltd = $form_data[ 'form_' . $day_key . '_LTD_or_WHSCC' ] ?? '';
                    $manual_km = $form_data[ 'form_' . $day_key . '_kms_manual' ] ?? '';
                    $dropdown_km = $form_data[ 'form_' . $day_key . '_kms_own_vehicle' ] ?? '';
                    $round_trip = $form_data[ 'form_' . $day_key . '_round_trip' ] ?? '';

                    $kms_value = $manual_km !== '' ? $manual_km : $dropdown_km;
                    if ( '' !== $kms_value && 'on' === $round_trip ) {
                        $kms_value .= ' (' . __( 'Round Trip', 'nbnu-expense-form' ) . ')';
                    }

                    $meals = [];
                    if ( 'on' === ( $form_data[ 'form_' . $day_key . '_meal_breakfast' ] ?? '' ) ) {
                        $meals[] = __( 'B', 'nbnu-expense-form' );
                    }
                    if ( 'on' === ( $form_data[ 'form_' . $day_key . '_meal_lunch' ] ?? '' ) ) {
                        $meals[] = __( 'L', 'nbnu-expense-form' );
                    }
                    if ( 'on' === ( $form_data[ 'form_' . $day_key . '_meal_supper' ] ?? '' ) ) {
                        $meals[] = __( 'S', 'nbnu-expense-form' );
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html( $day_names[ $day_key ] ); ?></td>
                        <td><?php echo esc_html( $date_value ); ?></td>
                        <td><?php echo esc_html( $travel_hours ); ?></td>
                        <td><?php echo esc_html( $meeting_hours ); ?></td>
                        <td><?php echo esc_html( $billing ); ?></td>
                        <td><?php echo esc_html( $day_off ?: __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
                        <td><?php echo esc_html( $ltd ?: __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
                        <td><?php echo esc_html( $kms_value ?: __( '0', 'nbnu-expense-form' ) ); ?></td>
                        <td><?php echo esc_html( implode( ', ', $meals ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2><?php esc_html_e( 'Travel Information', 'nbnu-expense-form' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Used Own Car:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_use_own_car'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Travelled From:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_travelled_from'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Travelled To:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_travelled_to'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Manual KM Entry:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( 'on' === ( $form_data['form_travel_destination_fredericton'] ?? '' ) ? __( 'Yes', 'nbnu-expense-form' ) : __( 'No', 'nbnu-expense-form' ) ); ?></td>
            </tr>
        </table>

        <h2><?php esc_html_e( 'Accommodation & Expenses', 'nbnu-expense-form' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Hotel Nights:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_hotel_number_nights'] ?? '0' ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Hotel Rate per Night:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_hotel_night_rates'] ?? '$0.00' ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Private Accommodation Nights:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_private_acc_number_nights'] ?? '0' ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Other Expenses:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_parking_taxi_etc'] ?? '$0.00' ); ?></td>
            </tr>
        </table>

        <h2><?php esc_html_e( 'Calculated Totals', 'nbnu-expense-form' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Total Hours (Travel + Meeting):', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_calc_total_hours_travel_meeting'] ?? '0.00' ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Hours Billed by Employer:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_calc_Less_hours_billed_by_employer'] ?? '0.00' ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Hours Paid:', 'nbnu-expense-form' ); ?></th>
                <td><?php echo esc_html( $form_data['form_calc_hours_paid'] ?? '0.00' ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Final Hours Payment:', 'nbnu-expense-form' ); ?></th>
                <td><strong><?php echo esc_html( $form_data['form_calc_final_hours_paid'] ?? '$0.00' ); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Mileage Total:', 'nbnu-expense-form' ); ?></th>
                <td><strong><?php echo esc_html( $form_data['form_calc_total_kms_using_own_vehicle'] ?? '$0.00' ); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Meals Total:', 'nbnu-expense-form' ); ?></th>
                <td><strong><?php echo esc_html( $form_data['form_calc_meals_total'] ?? '$0.00' ); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Hotel Total:', 'nbnu-expense-form' ); ?></th>
                <td><strong><?php echo esc_html( $form_data['form_calc_hotels_acc_total'] ?? '$0.00' ); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Private Accommodation Total:', 'nbnu-expense-form' ); ?></th>
                <td><strong><?php echo esc_html( $form_data['form_calc_private_acc_total'] ?? '$0.00' ); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Other Expenses Total:', 'nbnu-expense-form' ); ?></th>
                <td><strong><?php echo esc_html( $form_data['form_calc_others_total'] ?? '$0.00' ); ?></strong></td>
            </tr>
            <tr style="border-top: 2px solid #ddd;">
                <th style="font-size: 1.2em;"><?php esc_html_e( 'GRAND TOTAL:', 'nbnu-expense-form' ); ?></th>
                <td style="font-size: 1.2em;"><strong><?php echo esc_html( $form_data['form_calc_total_salary_expense_paid'] ?? '$0.00' ); ?></strong></td>
            </tr>
        </table>

        <?php if ( ! empty( $form_data['form_comments'] ) ) : ?>
            <h2><?php esc_html_e( 'Comments', 'nbnu-expense-form' ); ?></h2>
            <div class="comments-box">
                <?php echo nl2br( esc_html( $form_data['form_comments'] ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $form_data['uploaded_files'] ) && is_array( $form_data['uploaded_files'] ) ) : ?>
            <h2><?php esc_html_e( 'Uploaded Files', 'nbnu-expense-form' ); ?></h2>
            <ul>
                <?php foreach ( $form_data['uploaded_files'] as $file_url ) : ?>
                    <li><a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( basename( $file_url ) ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="submission-actions">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=nbnu-expense-admin' ) ); ?>" class="button">&larr; <?php esc_html_e( 'Back to All Submissions', 'nbnu-expense-form' ); ?></a>
        <button type="button" class="button button-primary" onclick="window.print()"><?php esc_html_e( 'Print', 'nbnu-expense-form' ); ?></button>
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
    .submission-actions,
    .wp-admin,
    #wpadminbar {
        display: none !important;
    }

    .wrap {
        margin: 0 !important;
    }
}
</style>
