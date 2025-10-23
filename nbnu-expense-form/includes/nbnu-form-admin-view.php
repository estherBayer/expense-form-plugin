<?php
/**
 * NBNU Form Admin Detail View Template
 * Displays individual form submission details and provides staff editing tools.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$day_keys = function_exists( 'nbnu_expense_get_day_keys' ) ? nbnu_expense_get_day_keys() : [ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ];
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

$print_url_args = [
    'page' => 'nbnu-expense-admin',
    'view' => 'submission',
    'id'   => $submission['id'],
    'format' => 'print',
];

if ( ! empty( $nbnu_submission_token ) ) {
    $print_url_args['token'] = rawurlencode( $nbnu_submission_token );
}

$print_url      = add_query_arg( $print_url_args, admin_url( 'admin.php' ) );
$print_auto_url = add_query_arg( 'auto', '1', $print_url );

if ( ! empty( $nbnu_print_view ) ) {
    $auto_print = isset( $_GET['auto'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    ?>
    <div class="wrap nbnu-print-view">
        <h1><?php printf( esc_html__( 'NBNU Expense Submission #%s', 'nbnu-expense-form' ), esc_html( $nbnu_submission_number ) ); ?></h1>
        <p><strong><?php esc_html_e( 'Submission Date:', 'nbnu-expense-form' ); ?></strong> <?php echo esc_html( $submission_date ); ?></p>
        <p><strong><?php esc_html_e( 'Status:', 'nbnu-expense-form' ); ?></strong> <?php echo esc_html( nbnu_expense_get_status_label( $submission['status'] ) ); ?></p>

        <h2><?php esc_html_e( 'Personal Information', 'nbnu-expense-form' ); ?></h2>
        <table class="widefat fixed">
            <tbody>
                <tr><th><?php esc_html_e( 'Name', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_name'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Meeting', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_meeting'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Dates', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_dates'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Email', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_member_email'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Address', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_address'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Employer', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_employer'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Classification', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_classifications'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Hourly Rate', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_hourly_rate'] ?? '' ); ?></td></tr>
            </tbody>
        </table>

        <h2><?php esc_html_e( 'Daily Breakdown', 'nbnu-expense-form' ); ?></h2>
        <table class="widefat fixed">
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

                    $travel_hours = $form_data[ 'form_' . $day_key . '_hours_travel' ] ?? '';
                    $meeting_hours = $form_data[ 'form_' . $day_key . '_hours_meeting' ] ?? '';
                    $billing = $form_data[ 'form_' . $day_key . '_employer_billing_NBNU' ] ?? '';
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
                        <td><?php echo esc_html( $day_off ); ?></td>
                        <td><?php echo esc_html( $ltd ); ?></td>
                        <td><?php echo esc_html( $kms_value ); ?></td>
                        <td><?php echo esc_html( implode( ', ', $meals ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2><?php esc_html_e( 'Totals', 'nbnu-expense-form' ); ?></h2>
        <table class="widefat fixed">
            <tbody>
                <tr><th><?php esc_html_e( 'Total Hours (Travel + Meeting)', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_total_hours_travel_meeting'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Hours Billed by Employer', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_Less_hours_billed_by_employer'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Hours Paid', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_hours_paid'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Final Hours Paid', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_final_hours_paid'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Mileage Total', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_total_kms_using_own_vehicle'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Meals Total', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_meals_total'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Hotel Total', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_hotels_acc_total'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Private Accommodation Total', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_private_acc_total'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Other Expenses', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_others_total'] ?? '' ); ?></td></tr>
                <tr><th><?php esc_html_e( 'Grand Total', 'nbnu-expense-form' ); ?></th><td><?php echo esc_html( $form_data['form_calc_total_salary_expense_paid'] ?? '' ); ?></td></tr>
            </tbody>
        </table>

        <?php if ( ! empty( $form_data['uploaded_files'] ) ) : ?>
            <h2><?php esc_html_e( 'Uploaded Files', 'nbnu-expense-form' ); ?></h2>
            <ul>
                <?php foreach ( (array) $form_data['uploaded_files'] as $file_url ) : ?>
                    <li><?php echo esc_html( basename( $file_url ) ); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <style>
        .nbnu-print-view table { margin-bottom: 20px; }
        .nbnu-print-view th { width: 220px; text-align: left; }
        .nbnu-print-view table, .nbnu-print-view th, .nbnu-print-view td { border: 1px solid #ccc; border-collapse: collapse; padding: 6px 10px; }
        @media print {
            #wpadminbar,
            #adminmenumain,
            .nbnu-admin-actions,
            .notice { display: none !important; }

            body.wp-admin #wpcontent { margin-left: 0; }
        }
    </style>
    <?php if ( $auto_print ) : ?>
        <script>window.print();</script>
    <?php endif; ?>
    <?php
    return;
}
?>

<div class="wrap nbnu-admin-submission">
    <h1><?php printf( esc_html__( 'NBNU Form Submission #%s', 'nbnu-expense-form' ), esc_html( $nbnu_submission_number ) ); ?></h1>

    <?php if ( ! empty( $nbnu_admin_notice ) ) : ?>
        <div class="notice notice-<?php echo 'error' === $nbnu_admin_notice['type'] ? 'error' : esc_attr( $nbnu_admin_notice['type'] ); ?>">
            <p><?php echo esc_html( $nbnu_admin_notice['message'] ); ?></p>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $nbnu_token_expired ) ) : ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'The edit link you used has expired. You can continue editing because you are logged in, but you should request a new link if needed.', 'nbnu-expense-form' ); ?></p>
        </div>
    <?php elseif ( ! empty( $nbnu_token_valid ) && ! empty( $submission['token_expiry'] ) ) : ?>
        <div class="notice notice-info">
            <p><?php printf( esc_html__( 'Secure edit link verified. Token expires on %s.', 'nbnu-expense-form' ), esc_html( date_i18n( get_option( 'date_format' ), (int) $submission['token_expiry'] ) ) ); ?></p>
        </div>
    <?php endif; ?>

    <div class="nbnu-submission-meta">
        <p><strong><?php esc_html_e( 'Submitted:', 'nbnu-expense-form' ); ?></strong> <?php echo esc_html( $submission_date ); ?></p>
        <p><strong><?php esc_html_e( 'Status:', 'nbnu-expense-form' ); ?></strong>
            <span class="status-badge status-<?php echo esc_attr( $nbnu_submission_status ); ?>"><?php echo esc_html( nbnu_expense_get_status_label( $nbnu_submission_status ) ); ?></span>
        </p>
        <p><strong><?php esc_html_e( 'Submission ID:', 'nbnu-expense-form' ); ?></strong> #<?php echo esc_html( $nbnu_submission_number ); ?></p>
    </div>

    <?php if ( ! empty( $form_data['uploaded_files'] ) ) : ?>
        <div class="nbnu-uploaded-files">
            <h2><?php esc_html_e( 'Uploaded Files', 'nbnu-expense-form' ); ?></h2>
            <ul>
                <?php foreach ( (array) $form_data['uploaded_files'] as $file_url ) : ?>
                    <li><a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( basename( $file_url ) ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="nbnu-admin-actions">
        <a href="<?php echo esc_url( $print_url ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Export PDF', 'nbnu-expense-form' ); ?></a>
        <button type="button" class="button button-secondary nbnu-print-button" data-print-url="<?php echo esc_url( $print_auto_url ); ?>"><?php esc_html_e( 'Print', 'nbnu-expense-form' ); ?></button>
    </div>

    <?php
    $nbnu_form_context  = 'admin';
    $nbnu_existing_data = $form_data;
    include plugin_dir_path( __FILE__ ) . 'nbnu-form.php';
    ?>

    <script>
        window.nbnuAdminSubmission = <?php echo wp_json_encode( $form_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ); ?>;
    </script>
</div>

<style>
    .nbnu-admin-submission .nbnu-submission-meta {
        margin-bottom: 20px;
        padding: 12px 16px;
        background: #f8f9ff;
        border: 1px solid #d0d7ff;
        border-radius: 4px;
    }

    .nbnu-admin-submission .nbnu-submission-meta p {
        margin: 0 0 8px;
    }

    .nbnu-admin-submission .status-badge {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .nbnu-admin-submission .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .nbnu-admin-submission .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .nbnu-admin-submission .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .nbnu-admin-actions {
        margin: 20px 0;
        display: flex;
        gap: 10px;
    }

    .nbnu-admin-review {
        border: 1px solid #dcdcde;
        padding: 16px;
        border-radius: 4px;
        background: #f9f9f9;
    }

    .nbnu-uploaded-files ul {
        list-style: disc;
        margin-left: 20px;
    }

    .nbnu-print-view .wrap { box-shadow: none; }
</style>
