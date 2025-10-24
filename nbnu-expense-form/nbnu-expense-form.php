<?php
/*
Plugin Name: NBNU Expense Form
Description: Standalone bilingual expense form plugin (WPML compatible) with admin editing interface.
Version: 1.1.0
Author: NBNU
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'NBNU_EXPENSE_FORM_VERSION' ) ) {
    define( 'NBNU_EXPENSE_FORM_VERSION', '1.1.0' );
}

/**
 * Register public assets so they can be enqueued on demand.
 */
function nbnu_expense_register_assets() {
    $style_path  = plugin_dir_path( __FILE__ ) . 'assets/nbnu-form.css';
    $script_path = plugin_dir_path( __FILE__ ) . 'assets/nbnu-form.js';

    $style_version  = file_exists( $style_path ) ? filemtime( $style_path ) : NBNU_EXPENSE_FORM_VERSION;
    $script_version = file_exists( $script_path ) ? filemtime( $script_path ) : NBNU_EXPENSE_FORM_VERSION;

    wp_register_style(
        'nbnu-expense-form',
        plugin_dir_url( __FILE__ ) . 'assets/nbnu-form.css',
        [],
        $style_version
    );

    wp_register_script(
        'nbnu-expense-form',
        plugin_dir_url( __FILE__ ) . 'assets/nbnu-form.js',
        [ 'jquery', 'jquery-ui-datepicker' ],
        $script_version,
        true
    );
}
add_action( 'init', 'nbnu_expense_register_assets' );

/**
 * Enqueue assets for the form and pass localized data to the script.
 *
 * @param string $context Context for the assets (public|admin).
 */
function nbnu_expense_enqueue_assets( $context = 'public' ) {
    if ( ! wp_script_is( 'nbnu-expense-form', 'registered' ) ) {
        nbnu_expense_register_assets();
    }

    wp_enqueue_style( 'nbnu-expense-form' );
    wp_enqueue_script( 'nbnu-expense-form' );

    wp_localize_script(
        'nbnu-expense-form',
        'nbnu_ajax',
        [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'nbnu_form_nonce' ),
            'context'  => $context,
            'strings'  => [
                'globalError'    => __( 'You are missing a few required fields. Please review the form!', 'nbnu-expense-form' ),
                'submit'         => __( 'Submit', 'nbnu-expense-form' ),
                'submitting'     => __( 'Submitting…', 'nbnu-expense-form' ),
                'genericError'   => __( 'There was an error submitting your form. Please try again.', 'nbnu-expense-form' ),
                'selectedFiles'  => __( 'Selected Files:', 'nbnu-expense-form' ),
                'requiredField'  => __( 'This field is required.', 'nbnu-expense-form' ),
                'invalidEmail'   => __( 'Please enter a valid email', 'nbnu-expense-form' ),
                'invalidNumber'  => __( 'Please enter a valid number.', 'nbnu-expense-form' ),
                'adminSuccess'   => __( 'Submission updated successfully.', 'nbnu-expense-form' ),
                'adminError'     => __( 'Unable to update submission. Please review the form and try again.', 'nbnu-expense-form' ),
            ],
        ]
    );
}

/**
 * Shortcode output for the public expense form.
 *
 * @return string
 */
function nbnu_expense_shortcode() {
    nbnu_expense_enqueue_assets();

    ob_start();
    include plugin_dir_path( __FILE__ ) . 'includes/nbnu-form.php';

    return ob_get_clean();
}
add_shortcode( 'nbnu_expense_form', 'nbnu_expense_shortcode' );

add_action( 'wp_ajax_submit_nbnu_form', 'nbnu_handle_form_submit' );
add_action( 'wp_ajax_nopriv_submit_nbnu_form', 'nbnu_handle_form_submit' );

add_action(
    'admin_enqueue_scripts',
    function ( $hook ) {
        if ( 'nbnu-expense_page_nbnu-expense-admin' !== $hook ) {
            return;
        }

        nbnu_expense_enqueue_assets( 'admin' );
    }
);

add_action( 'admin_post_nbnu_expense_update_submission', 'nbnu_expense_handle_admin_submission_update' );

/**
 * Handle the AJAX submission for the expense form.
 */
function nbnu_handle_form_submit() {
    check_ajax_referer( 'nbnu_form_nonce', 'nonce' );

    $raw_data  = wp_unslash( $_POST );
    $form_data = [];

    foreach ( $raw_data as $key => $value ) {
        if ( in_array( $key, [ 'action', 'nonce' ], true ) ) {
            continue;
        }

        $form_data[ $key ] = nbnu_expense_sanitize_field( $key, $value );
    }

    $validation = nbnu_expense_validate_form_data( $form_data );

    if ( is_wp_error( $validation ) ) {
        wp_send_json_error(
            [ 'message' => $validation->get_error_message() ]
        );
    }

    $post_title = ! empty( $form_data['form_name'] )
        ? sprintf( __( 'Expense submission from %s', 'nbnu-expense-form' ), $form_data['form_name'] )
        : __( 'Expense Submission', 'nbnu-expense-form' );

    $post_id = wp_insert_post(
        [
            'post_type'   => 'nbnu_expense',
            'post_title'  => $post_title,
            'post_status' => 'private',
        ],
        true
    );

    if ( is_wp_error( $post_id ) || ! $post_id ) {
        wp_send_json_error(
            [ 'message' => __( 'Error saving form. Please try again.', 'nbnu-expense-form' ) ]
        );
    }

    $uploaded_files = nbnu_expense_handle_uploads( $post_id );

    if ( ! empty( $uploaded_files ) ) {
        $form_data['uploaded_files'] = $uploaded_files;
    }

    $submission_number = nbnu_expense_get_next_submission_number();
    $token_data        = nbnu_expense_generate_edit_token( $post_id );

    update_post_meta( $post_id, 'nbnu_submission_number', $submission_number );
    update_post_meta( $post_id, 'nbnu_submission_status', 'pending' );
    update_post_meta( $post_id, 'nbnu_submission_token', $token_data['token'] );
    update_post_meta( $post_id, 'nbnu_submission_token_expiry', $token_data['expires'] );

    $form_data['submission_number'] = $submission_number;

    wp_update_post(
        [
            'ID'           => $post_id,
            'post_content' => wp_json_encode( $form_data ),
        ]
    );

    nbnu_expense_send_admin_notification( $post_id, $form_data, $submission_number, $token_data['token'] );

    wp_send_json_success(
        [
            'message'           => __( 'Form submitted successfully!', 'nbnu-expense-form' ),
            'submissionNumber'  => $submission_number,
        ]
    );
}

/**
 * Sanitize individual field values according to type.
 *
 * @param string $key   Field key.
 * @param mixed  $value Raw value.
 *
 * @return string|array Sanitized value.
 */
function nbnu_expense_sanitize_field( $key, $value ) {
    if ( is_array( $value ) ) {
        $sanitized = [];

        foreach ( $value as $sub_key => $sub_value ) {
            $sanitized_key = is_string( $sub_key ) ? sanitize_key( $sub_key ) : $sub_key;
            $sanitized[ $sanitized_key ] = nbnu_expense_sanitize_field( (string) $sub_key, $sub_value );
        }

        return $sanitized;
    }

    $value = wp_strip_all_tags( $value );

    if ( false !== stripos( $key, 'email' ) ) {
        return sanitize_email( $value );
    }

    if ( false !== stripos( $key, 'comments' ) ) {
        return sanitize_textarea_field( $value );
    }

    if ( preg_match( '/(total|amount|rate|hours|kms?|nights|number|calc)/i', $key ) ) {
        return preg_replace( '/[^0-9,\.\$\-]/', '', $value );
    }

    return sanitize_text_field( $value );
}

/**
 * Validate sanitized form data before persisting.
 *
 * @param array $form_data Sanitized data.
 *
 * @return true|WP_Error
 */
function nbnu_expense_validate_form_data( $form_data ) {
    $required_fields = [
        'form_meeting',
        'form_dates',
        'form_name',
        'form_address',
        'form_employer',
        'form_hourly_rate',
        'date_month',
        'date_day',
        'date_year',
        'form_classifications',
        'form_provincial_or_local_office',
    ];

    foreach ( $required_fields as $field_key ) {
        if ( empty( $form_data[ $field_key ] ) ) {
            return new WP_Error( 'nbnu_expense_missing_field', __( 'Required form data is missing. Please complete all required fields.', 'nbnu-expense-form' ) );
        }
    }

    if ( isset( $form_data['form_provincial_or_local_office'] ) && 'Local Office' === $form_data['form_provincial_or_local_office'] ) {
        if ( empty( $form_data['form_provincial_or_local_office_email'] ) || ! is_email( $form_data['form_provincial_or_local_office_email'] ) ) {
            return new WP_Error( 'nbnu_expense_invalid_email', __( 'A valid local office email address is required.', 'nbnu-expense-form' ) );
        }
    }

    $day_keys   = nbnu_expense_get_day_keys();
    $has_day    = false;
    $numeric_fields = [
        'form_hourly_rate',
        'form_calc_total_hours_travel_meeting',
        'form_calc_Less_hours_billed_by_employer',
        'form_calc_hours_paid',
        'form_calc_final_hours_paid',
        'form_calc_total_kms_using_own_vehicle',
        'form_calc_meals_total',
        'form_calc_hotels_acc_total',
        'form_calc_private_acc_total',
        'form_calc_others_total',
        'form_calc_total_salary_expense_paid',
        'form_hotel_number_nights',
        'form_hotel_night_rates',
        'form_private_acc_number_nights',
        'form_parking_taxi_etc',
    ];

    foreach ( $day_keys as $day_key ) {
        if ( ! empty( $form_data[ 'form_' . $day_key . '_date' ] ) ) {
            $has_day = true;
        }

        $numeric_fields[] = 'form_' . $day_key . '_hours_travel';
        $numeric_fields[] = 'form_' . $day_key . '_hours_meeting';
        $numeric_fields[] = 'form_' . $day_key . '_kms_manual';
        $numeric_fields[] = 'form_' . $day_key . '_kms_own_vehicle';
    }

    if ( ! $has_day ) {
        return new WP_Error( 'nbnu_expense_missing_day', __( 'At least one day entry is required.', 'nbnu-expense-form' ) );
    }

    foreach ( $numeric_fields as $field_key ) {
        if ( ! isset( $form_data[ $field_key ] ) || '' === $form_data[ $field_key ] ) {
            continue;
        }

        if ( null === nbnu_expense_parse_number( $form_data[ $field_key ] ) ) {
            return new WP_Error( 'nbnu_expense_invalid_number', __( 'Numeric fields must contain valid numbers.', 'nbnu-expense-form' ) );
        }
    }

    return true;
}

/**
 * Return canonical day keys used throughout the form.
 *
 * @return array
 */
function nbnu_expense_get_day_keys() {
    return [ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ];
}

/**
 * Parse a numeric string into a float value.
 *
 * @param mixed $value Raw numeric representation.
 *
 * @return float|null
 */
function nbnu_expense_parse_number( $value ) {
    if ( is_numeric( $value ) ) {
        return (float) $value;
    }

    if ( ! is_string( $value ) ) {
        return null;
    }

    $normalized = preg_replace( '/[^0-9,.-]/', '', $value );
    $normalized = str_replace( ',', '', $normalized );

    if ( '' === $normalized || ! is_numeric( $normalized ) ) {
        return null;
    }

    return (float) $normalized;
}

/**
 * Handle file uploads associated with the expense submission.
 *
 * @param int $post_id Post ID.
 *
 * @return array
 */
function nbnu_expense_handle_uploads( $post_id ) {
    if ( empty( $_FILES['form_files'] ) || empty( $_FILES['form_files']['name'] ) ) {
        return [];
    }

    $files          = $_FILES['form_files'];
    $uploaded_files = [];

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $allowed_mimes = apply_filters(
        'nbnu_expense_allowed_mimes',
        [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]
    );

    foreach ( $files['name'] as $index => $name ) {
        if ( empty( $name ) || UPLOAD_ERR_OK !== $files['error'][ $index ] ) {
            continue;
        }

        $sanitized_name = sanitize_file_name( $name );
        $file_array     = [
            'name'     => $sanitized_name,
            'type'     => $files['type'][ $index ],
            'tmp_name' => $files['tmp_name'][ $index ],
            'error'    => 0,
            'size'     => (int) $files['size'][ $index ],
        ];

        $file_check = wp_check_filetype_and_ext( $file_array['tmp_name'], $sanitized_name, $allowed_mimes );

        if ( empty( $file_check['ext'] ) || empty( $file_check['type'] ) ) {
            continue;
        }

        $overrides = [
            'test_form' => false,
            'mimes'     => $allowed_mimes,
        ];

        $result = wp_handle_upload( $file_array, $overrides );

        if ( isset( $result['error'] ) || empty( $result['file'] ) ) {
            continue;
        }

        $attachment = [
            'post_mime_type' => $result['type'],
            'post_title'     => sanitize_text_field( pathinfo( $sanitized_name, PATHINFO_FILENAME ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attachment_id = wp_insert_attachment( $attachment, $result['file'], $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            continue;
        }

        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $result['file'] );
        wp_update_attachment_metadata( $attachment_id, $attachment_data );

        $file_url = wp_get_attachment_url( $attachment_id );

        if ( $file_url ) {
            $uploaded_files[] = esc_url_raw( $file_url );
        }
    }

    return $uploaded_files;
}

/**
 * Retrieve the next incremental submission number.
 *
 * @return int
 */
function nbnu_expense_get_next_submission_number() {
    $last_number = (int) get_option( 'nbnu_expense_last_submission_number', 0 );
    $next        = $last_number + 1;

    update_option( 'nbnu_expense_last_submission_number', $next, false );

    return $next;
}

/**
 * Generate a secure edit token for staff use.
 *
 * @param int $post_id Submission post ID.
 *
 * @return array{
 *     token: string,
 *     expires: int,
 * }
 */
function nbnu_expense_generate_edit_token( $post_id ) {
    $token   = wp_generate_password( 32, false, false );
    $expires = time() + ( 30 * DAY_IN_SECONDS );

    return [
        'token'   => apply_filters( 'nbnu_expense_edit_token', $token, $post_id ),
        'expires' => $expires,
    ];
}

/**
 * Return the configured admin notification email.
 *
 * @return string
 */
function nbnu_expense_get_notification_email() {
    $stored = get_option( 'nbnu_expense_admin_email', '' );

    if ( $stored && is_email( $stored ) ) {
        return $stored;
    }

    return get_option( 'admin_email' );
}

/**
 * Format a numeric amount into localized currency.
 *
 * @param mixed $amount Amount to format.
 *
 * @return string
 */
function nbnu_expense_format_currency( $amount ) {
    $value = nbnu_expense_parse_number( $amount );

    if ( null === $value ) {
        return '$0.00';
    }

    $formatted = number_format_i18n( $value, 2 );

    return sprintf( '$%s', $formatted );
}

/**
 * Send notification email to staff when a submission is created.
 *
 * @param int    $post_id           Post ID.
 * @param array  $form_data         Sanitized form data.
 * @param int    $submission_number Submission number.
 * @param string $token             Edit token.
 */
function nbnu_expense_send_admin_notification( $post_id, $form_data, $submission_number, $token ) {
    $email = nbnu_expense_get_notification_email();

    if ( empty( $email ) ) {
        return;
    }

    $subject = sprintf( __( '[NBNU] New Expense Form Submission [#%s]', 'nbnu-expense-form' ), $submission_number );
    $date    = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
    $name    = $form_data['form_name'] ?? '';
    $meeting = $form_data['form_meeting'] ?? '';
    $total   = nbnu_expense_format_currency( $form_data['form_calc_total_salary_expense_paid'] ?? '' );

    $edit_link = add_query_arg(
        [
            'page'  => 'nbnu-expense-admin',
            'view'  => 'submission',
            'id'    => $post_id,
            'token' => rawurlencode( $token ),
        ],
        admin_url( 'admin.php' )
    );

    $rows = [
        [ __( 'Submission ID', 'nbnu-expense-form' ), '#' . $submission_number ],
        [ __( 'Submission Date', 'nbnu-expense-form' ), $date ],
        [ __( 'Member Name', 'nbnu-expense-form' ), $name ],
        [ __( 'Meeting Name', 'nbnu-expense-form' ), $meeting ],
        [ __( 'Hours Paid', 'nbnu-expense-form' ), $form_data['form_calc_hours_paid'] ?? '' ],
        [ __( 'Mileage Total', 'nbnu-expense-form' ), nbnu_expense_format_currency( $form_data['form_calc_total_kms_using_own_vehicle'] ?? '' ) ],
        [ __( 'Meals Total', 'nbnu-expense-form' ), nbnu_expense_format_currency( $form_data['form_calc_meals_total'] ?? '' ) ],
        [ __( 'Hotel Total', 'nbnu-expense-form' ), nbnu_expense_format_currency( $form_data['form_calc_hotels_acc_total'] ?? '' ) ],
        [ __( 'Private Accommodation Total', 'nbnu-expense-form' ), nbnu_expense_format_currency( $form_data['form_calc_private_acc_total'] ?? '' ) ],
        [ __( 'Other Expenses', 'nbnu-expense-form' ), nbnu_expense_format_currency( $form_data['form_calc_others_total'] ?? '' ) ],
        [ __( 'Grand Total', 'nbnu-expense-form' ), $total ],
    ];

    $table_rows = '';

    foreach ( $rows as $row ) {
        $table_rows .= sprintf(
            '<tr><th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">%s</th><td style="padding:6px 12px;border:1px solid #ddd;">%s</td></tr>',
            esc_html( $row[0] ),
            esc_html( $row[1] )
        );
    }

    $summary_table = sprintf( '<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;width:100%%;">%s</table>', $table_rows );

    $day_rows   = '';
    $day_labels = [
        'sun' => __( 'Sunday', 'nbnu-expense-form' ),
        'mon' => __( 'Monday', 'nbnu-expense-form' ),
        'tue' => __( 'Tuesday', 'nbnu-expense-form' ),
        'wed' => __( 'Wednesday', 'nbnu-expense-form' ),
        'thu' => __( 'Thursday', 'nbnu-expense-form' ),
        'fri' => __( 'Friday', 'nbnu-expense-form' ),
        'sat' => __( 'Saturday', 'nbnu-expense-form' ),
    ];

    foreach ( nbnu_expense_get_day_keys() as $day_key ) {
        $date_value = $form_data[ 'form_' . $day_key . '_date' ] ?? '';

        if ( '' === $date_value ) {
            continue;
        }

        $travel_hours = $form_data[ 'form_' . $day_key . '_hours_travel' ] ?? '';
        $meeting_hours = $form_data[ 'form_' . $day_key . '_hours_meeting' ] ?? '';
        $billing = $form_data[ 'form_' . $day_key . '_employer_billing_NBNU' ] ?? '';
        $day_off = $form_data[ 'form_' . $day_key . '_day_off' ] ?? '';
        $ltd = $form_data[ 'form_' . $day_key . '_LTD_or_WHSCC' ] ?? '';
        $kms_manual = $form_data[ 'form_' . $day_key . '_kms_manual' ] ?? '';
        $kms_dropdown = $form_data[ 'form_' . $day_key . '_kms_own_vehicle' ] ?? '';
        $round_trip = $form_data[ 'form_' . $day_key . '_round_trip' ] ?? '';

        $km_display = '' !== $kms_manual ? $kms_manual : $kms_dropdown;

        if ( '' !== $km_display && 'on' === $round_trip ) {
            $km_display .= ' (' . __( 'Round Trip', 'nbnu-expense-form' ) . ')';
        }

        $meal_labels = [];

        if ( 'on' === ( $form_data[ 'form_' . $day_key . '_meal_breakfast' ] ?? '' ) ) {
            $meal_labels[] = __( 'Breakfast', 'nbnu-expense-form' );
        }

        if ( 'on' === ( $form_data[ 'form_' . $day_key . '_meal_lunch' ] ?? '' ) ) {
            $meal_labels[] = __( 'Lunch', 'nbnu-expense-form' );
        }

        if ( 'on' === ( $form_data[ 'form_' . $day_key . '_meal_supper' ] ?? '' ) ) {
            $meal_labels[] = __( 'Supper', 'nbnu-expense-form' );
        }

        $meals_display = implode( ', ', $meal_labels );

        if ( '' === $km_display ) {
            $km_display = __( 'N/A', 'nbnu-expense-form' );
        }

        if ( '' === $meals_display ) {
            $meals_display = __( 'None', 'nbnu-expense-form' );
        }

        $day_rows .= sprintf(
            '<tr>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
                '<td style="padding:6px 12px;border:1px solid #ddd;">%s</td>' .
            '</tr>',
            esc_html( $day_labels[ $day_key ] ?? ucfirst( $day_key ) ),
            esc_html( $date_value ),
            esc_html( $travel_hours ),
            esc_html( $meeting_hours ),
            esc_html( $billing ),
            esc_html( $day_off ),
            esc_html( $ltd ),
            esc_html( $km_display ),
            esc_html( $meals_display )
        );
    }

    $message  = '<p>' . esc_html__( 'A new expense submission has been received with the following summary:', 'nbnu-expense-form' ) . '</p>';
    $message .= $summary_table;

    if ( $day_rows ) {
        $message .= '<h3 style="margin-top:24px;">' . esc_html__( 'Daily Summary', 'nbnu-expense-form' ) . '</h3>';
        $message .= '<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:12px 0;width:100%;">';
        $message .= '<thead><tr>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Day', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Date', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Travel Hours', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Meeting Hours', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Employer Billing', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Day Off', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'LTD/WHSCC', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Mileage', 'nbnu-expense-form' ) . '</th>' .
            '<th align="left" style="padding:6px 12px;background:#f9f9f9;border:1px solid #ddd;">' . esc_html__( 'Meals', 'nbnu-expense-form' ) . '</th>' .
            '</tr></thead>';
        $message .= '<tbody>' . $day_rows . '</tbody></table>';
    }

    $message .= '<p><a style="display:inline-block;padding:10px 16px;background:#0073aa;color:#fff;text-decoration:none;border-radius:4px;" href="' . esc_url( $edit_link ) . '">' . esc_html__( 'View & Edit Submission', 'nbnu-expense-form' ) . '</a></p>';

    if ( ! empty( $form_data['uploaded_files'] ) ) {
        $message .= '<p><strong>' . esc_html__( 'Attached Files', 'nbnu-expense-form' ) . ':</strong></p><ul style="margin:0 0 16px 20px;">';

        foreach ( (array) $form_data['uploaded_files'] as $file_url ) {
            $message .= '<li><a href="' . esc_url( $file_url ) . '">' . esc_html( basename( $file_url ) ) . '</a></li>';
        }

        $message .= '</ul>';
    }

    wp_mail(
        $email,
        $subject,
        $message,
        [ 'Content-Type: text/html; charset=UTF-8' ]
    );
}

/**
 * Validate that a token is still active for a given submission.
 *
 * @param int    $post_id Post ID.
 * @param string $token   Token provided.
 *
 * @return bool
 */
function nbnu_expense_validate_edit_token( $post_id, $token ) {
    if ( empty( $token ) ) {
        return false;
    }

    $stored_token  = get_post_meta( $post_id, 'nbnu_submission_token', true );
    $token_expiry  = (int) get_post_meta( $post_id, 'nbnu_submission_token_expiry', true );

    if ( ! $stored_token || ! hash_equals( $stored_token, $token ) ) {
        return false;
    }

    if ( $token_expiry && time() > $token_expiry ) {
        return false;
    }

    return true;
}

/**
 * Send a status update email to the member if an address is available.
 *
 * @param string $status            New status.
 * @param array  $form_data         Submission data.
 * @param int    $submission_number Submission number.
 */
function nbnu_expense_send_status_email( $status, $form_data, $submission_number ) {
    $email = $form_data['form_member_email'] ?? ( $form_data['form_email'] ?? ( $form_data['form_provincial_or_local_office_email'] ?? '' ) );

    if ( empty( $email ) || ! is_email( $email ) ) {
        return;
    }

    $status_labels = nbnu_expense_get_status_options();
    $label         = $status_labels[ $status ] ?? ucfirst( $status );

    $subject = sprintf( __( 'Your NBNU expense submission #%s is now %s', 'nbnu-expense-form' ), $submission_number, strtolower( $label ) );

    $message  = '<p>' . esc_html__( 'Hello,', 'nbnu-expense-form' ) . '</p>';
    $message .= '<p>' . sprintf( esc_html__( 'Your NBNU expense form submission #%1$s has been marked as %2$s.', 'nbnu-expense-form' ), esc_html( $submission_number ), esc_html( strtolower( $label ) ) ) . '</p>';

    if ( 'rejected' === $status ) {
        $message .= '<p>' . esc_html__( 'A staff member will contact you if additional details are required.', 'nbnu-expense-form' ) . '</p>';
    } elseif ( 'approved' === $status ) {
        $message .= '<p>' . esc_html__( 'The submission has been approved and will move forward for payment processing.', 'nbnu-expense-form' ) . '</p>';
    }

    $message .= '<p>' . esc_html__( 'Thank you.', 'nbnu-expense-form' ) . '</p>';

    wp_mail(
        $email,
        $subject,
        $message,
        [ 'Content-Type: text/html; charset=UTF-8' ]
    );
}

/**
 * Return the available submission statuses.
 *
 * @return array
 */
function nbnu_expense_get_status_options() {
    return [
        'pending'  => __( 'Pending', 'nbnu-expense-form' ),
        'approved' => __( 'Approved', 'nbnu-expense-form' ),
        'rejected' => __( 'Rejected', 'nbnu-expense-form' ),
    ];
}

/**
 * Return all known form field keys used for storage.
 *
 * @return array
 */
function nbnu_expense_get_known_fields() {
    $fields = [
        'form_meeting',
        'form_dates',
        'form_name',
        'form_address',
        'form_employer',
        'form_hourly_rate',
        'form_classifications',
        'form_meeting_out_of_province',
        'form_provincial_or_local_office',
        'form_provincial_or_local_office_email',
        'form_travelled_from',
        'form_travelled_to',
        'form_travel_destination_fredericton',
        'form_use_own_car',
        'form_hotel_number_nights',
        'form_hotel_night_rates',
        'form_private_acc_number_nights',
        'form_parking_taxi_etc',
        'form_comments',
        'form_member_email',
        'date_month',
        'date_day',
        'date_year',
        'form_calc_total_hours_travel_meeting',
        'form_calc_Less_hours_billed_by_employer',
        'form_calc_hours_paid',
        'form_calc_final_hours_paid',
        'form_calc_total_kms_using_own_vehicle',
        'form_calc_meals_total',
        'form_calc_hotels_acc_total',
        'form_calc_private_acc_total',
        'form_calc_others_total',
        'form_calc_total_salary_expense_paid',
    ];

    foreach ( nbnu_expense_get_day_keys() as $day ) {
        $fields[] = 'form_' . $day . '_date';
        $fields[] = 'form_' . $day . '_hours_travel';
        $fields[] = 'form_' . $day . '_hours_meeting';
        $fields[] = 'form_' . $day . '_employer_billing_NBNU';
        $fields[] = 'form_' . $day . '_day_off';
        $fields[] = 'form_' . $day . '_LTD_or_WHSCC';
        $fields[] = 'form_' . $day . '_kms_own_vehicle';
        $fields[] = 'form_' . $day . '_kms_manual';
        $fields[] = 'form_' . $day . '_round_trip';
        $fields[] = 'form_' . $day . '_meal_breakfast';
        $fields[] = 'form_' . $day . '_meal_lunch';
        $fields[] = 'form_' . $day . '_meal_supper';
    }

    return array_unique( $fields );
}

/**
 * Persist an admin notice for the next page load.
 *
 * @param string $type    Notice type (success|error|warning|info).
 * @param string $message Message to display.
 */
function nbnu_expense_set_admin_notice( $type, $message ) {
    set_transient(
        'nbnu_expense_notice_' . get_current_user_id(),
        [
            'type'    => $type,
            'message' => $message,
        ],
        MINUTE_IN_SECONDS
    );
}

/**
 * Retrieve the stored admin notice for the current user.
 *
 * @return array|null
 */
function nbnu_expense_get_admin_notice() {
    $key    = 'nbnu_expense_notice_' . get_current_user_id();
    $notice = get_transient( $key );

    if ( $notice ) {
        delete_transient( $key );
    }

    return $notice;
}

add_action(
    'init',
    function () {
        register_post_type(
            'nbnu_expense',
            [
                'labels' => [
                    'name'          => __( 'Expense Forms', 'nbnu-expense-form' ),
                    'singular_name' => __( 'Expense Form', 'nbnu-expense-form' ),
                ],
                'public'       => false,
                'show_ui'      => true,
                'supports'     => [ 'title' ],
                'show_in_rest' => false,
                'menu_icon'    => 'dashicons-clipboard',
            ]
        );
    }
);

add_action(
    'admin_menu',
    function () {
        add_submenu_page(
            'edit.php?post_type=nbnu_expense',
            __( 'NBNU Form Submissions', 'nbnu-expense-form' ),
            __( 'Admin View', 'nbnu-expense-form' ),
            'manage_options',
            'nbnu-expense-admin',
            'nbnu_expense_render_admin_page'
        );
    }
);

/**
 * Render the admin submissions list or single submission view.
 */
function nbnu_expense_render_admin_page() {
    $view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $id   = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $nbnu_admin_notice = isset( $_GET['nbnu_notice'] ) ? nbnu_expense_get_admin_notice() : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ( 'submission' === $view && $id ) {
        $submission = nbnu_expense_get_submission( $id );

        if ( ! $submission ) {
            printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'Submission not found.', 'nbnu-expense-form' ) );
            return;
        }

        $form_data                 = $submission['form_data'];
        $nbnu_submission_number    = $submission['submission_number'] ?: $submission['id'];
        $nbnu_staff_notes          = get_post_meta( $submission['id'], 'nbnu_staff_notes', true );
        $nbnu_submission_status    = $submission['status'];
        $nbnu_submission_token_raw = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $nbnu_submission_token     = '';
        $nbnu_token_valid          = false;

        if ( $nbnu_submission_token_raw && nbnu_expense_validate_edit_token( $submission['id'], $nbnu_submission_token_raw ) ) {
            $nbnu_submission_token = $nbnu_submission_token_raw;
            $nbnu_token_valid      = true;
        }

        $nbnu_token_expired = false;

        if ( $nbnu_submission_token_raw && ! $nbnu_token_valid ) {
            $nbnu_token_expired = true;
        }

        $nbnu_print_view = isset( $_GET['format'] ) && 'print' === sanitize_key( wp_unslash( $_GET['format'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        include plugin_dir_path( __FILE__ ) . 'includes/nbnu-form-admin-view.php';
        return;
    }

    $submissions = nbnu_expense_get_submissions();
    include plugin_dir_path( __FILE__ ) . 'includes/nbnu-form-admin-list.php';
}

/**
 * Handle updates performed through the admin edit screen.
 */
function nbnu_expense_handle_admin_submission_update() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You are not allowed to edit submissions.', 'nbnu-expense-form' ) );
    }

    check_admin_referer( 'nbnu_expense_update_submission' );

    $post_id = isset( $_POST['submission_id'] ) ? absint( wp_unslash( $_POST['submission_id'] ) ) : 0;

    $redirect = add_query_arg(
        [
            'page' => 'nbnu-expense-admin',
        ],
        admin_url( 'admin.php' )
    );

    if ( ! $post_id ) {
        nbnu_expense_set_admin_notice( 'error', __( 'Invalid submission.', 'nbnu-expense-form' ) );
        wp_safe_redirect( add_query_arg( 'nbnu_notice', 1, $redirect ) );
        exit;
    }

    $submission = nbnu_expense_get_submission( $post_id );

    if ( ! $submission ) {
        nbnu_expense_set_admin_notice( 'error', __( 'Submission could not be found.', 'nbnu-expense-form' ) );
        wp_safe_redirect( add_query_arg( 'nbnu_notice', 1, $redirect ) );
        exit;
    }

    $raw         = wp_unslash( $_POST );
    $known_keys  = nbnu_expense_get_known_fields();
    $form_data   = [];

    foreach ( $known_keys as $key ) {
        $form_data[ $key ] = $submission['form_data'][ $key ] ?? '';
    }

    foreach ( $known_keys as $key ) {
        if ( isset( $raw[ $key ] ) ) {
            $form_data[ $key ] = nbnu_expense_sanitize_field( $key, $raw[ $key ] );
        } else {
            $form_data[ $key ] = '';
        }
    }

    if ( ! empty( $submission['form_data']['uploaded_files'] ) ) {
        $form_data['uploaded_files'] = (array) $submission['form_data']['uploaded_files'];
    }

    if ( ! empty( $submission['submission_number'] ) ) {
        $form_data['submission_number'] = $submission['submission_number'];
    }

    $validation = nbnu_expense_validate_form_data( $form_data );

    if ( is_wp_error( $validation ) ) {
        nbnu_expense_set_admin_notice( 'error', $validation->get_error_message() );
        $error_url = add_query_arg(
            [
                'page' => 'nbnu-expense-admin',
                'view' => 'submission',
                'id'   => $post_id,
                'nbnu_notice' => 1,
            ],
            admin_url( 'admin.php' )
        );

        if ( ! empty( $raw['nbnu_submission_token'] ) ) {
            $error_url = add_query_arg( 'token', rawurlencode( sanitize_text_field( $raw['nbnu_submission_token'] ) ), $error_url );
        }

        wp_safe_redirect( $error_url );
        exit;
    }

    $content = wp_json_encode( $form_data );

    if ( false === $content ) {
        nbnu_expense_set_admin_notice( 'error', __( 'Unable to encode submission data.', 'nbnu-expense-form' ) );
        wp_safe_redirect( add_query_arg( 'nbnu_notice', 1, $redirect ) );
        exit;
    }

    $updated = wp_update_post(
        [
            'ID'           => $post_id,
            'post_content' => $content,
        ],
        true
    );

    if ( is_wp_error( $updated ) ) {
        nbnu_expense_set_admin_notice( 'error', __( 'Unable to update the submission record.', 'nbnu-expense-form' ) );
        wp_safe_redirect( add_query_arg( 'nbnu_notice', 1, $redirect ) );
        exit;
    }

    $allowed_statuses = array_keys( nbnu_expense_get_status_options() );
    $new_status       = isset( $raw['nbnu_submission_status'] ) ? sanitize_key( $raw['nbnu_submission_status'] ) : $submission['status'];

    if ( ! in_array( $new_status, $allowed_statuses, true ) ) {
        $new_status = $submission['status'];
    }

    update_post_meta( $post_id, 'nbnu_submission_status', $new_status );

    if ( isset( $raw['nbnu_staff_notes'] ) ) {
        update_post_meta( $post_id, 'nbnu_staff_notes', sanitize_textarea_field( $raw['nbnu_staff_notes'] ) );
    }

    if ( $new_status !== $submission['status'] ) {
        nbnu_expense_send_status_email( $new_status, $form_data, $submission['submission_number'] ?: $post_id );
    }

    nbnu_expense_set_admin_notice( 'success', __( 'Submission updated successfully.', 'nbnu-expense-form' ) );

    $success_url = add_query_arg(
        [
            'page' => 'nbnu-expense-admin',
            'view' => 'submission',
            'id'   => $post_id,
            'nbnu_notice' => 1,
        ],
        admin_url( 'admin.php' )
    );

    if ( ! empty( $raw['nbnu_submission_token'] ) ) {
        $success_url = add_query_arg( 'token', rawurlencode( sanitize_text_field( $raw['nbnu_submission_token'] ) ), $success_url );
    }

    wp_safe_redirect( $success_url );
    exit;
}

/**
 * Fetch a single submission entry.
 *
 * @param int $id Submission (post) ID.
 *
 * @return array|null
 */
function nbnu_expense_get_submission( $id ) {
    $post = get_post( $id );

    if ( ! $post || 'nbnu_expense' !== $post->post_type ) {
        return null;
    }

    $form_data = json_decode( $post->post_content, true );

    if ( ! is_array( $form_data ) ) {
        $form_data = [];
    }

    $submission_number = (int) get_post_meta( $post->ID, 'nbnu_submission_number', true );
    if ( $submission_number && empty( $form_data['submission_number'] ) ) {
        $form_data['submission_number'] = $submission_number;
    }

    $token_expiry = (int) get_post_meta( $post->ID, 'nbnu_submission_token_expiry', true );

    $status = get_post_meta( $post->ID, 'nbnu_submission_status', true );

    if ( empty( $status ) ) {
        $status = 'pending';
    }

    return [
        'id'               => $post->ID,
        'submission_date'  => get_date_from_gmt( $post->post_date_gmt, 'Y-m-d H:i:s' ),
        'status'           => $status,
        'form_data'        => $form_data,
        'submission_number'=> $submission_number,
        'token_expiry'     => $token_expiry,
    ];
}

/**
 * Return a translated status label for submissions.
 *
 * @param string $status Raw status slug.
 *
 * @return string
 */
function nbnu_expense_get_status_label( $status ) {
    $status = strtolower( (string) $status );

    $map = nbnu_expense_get_status_options();

    return $map[ $status ] ?? ucfirst( $status );
}

/**
 * Retrieve all expense submissions ordered by date.
 *
 * @return array
 */
function nbnu_expense_get_submissions() {
    $query = new WP_Query(
        [
            'post_type'      => 'nbnu_expense',
            'post_status'    => 'private',
            'posts_per_page' => 50,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]
    );

    $submissions = [];

    foreach ( $query->posts as $post ) {
        $submission = nbnu_expense_get_submission( $post->ID );

        if ( $submission ) {
            $submissions[] = $submission;
        }
    }

    return $submissions;
}

add_action(
    'plugins_loaded',
    function () {
        load_plugin_textdomain( 'nbnu-expense-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
);
