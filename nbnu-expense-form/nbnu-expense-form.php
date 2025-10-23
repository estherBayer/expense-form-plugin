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
add_action( 'wp_enqueue_scripts', 'nbnu_expense_register_assets' );

/**
 * Enqueue assets for the front-end form and pass localized data to the script.
 */
function nbnu_expense_enqueue_assets() {
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
            'strings'  => [
                'globalError'    => __( 'You are missing a few required fields. Please review the form!', 'nbnu-expense-form' ),
                'submit'         => __( 'Submit', 'nbnu-expense-form' ),
                'submitting'     => __( 'Submitting…', 'nbnu-expense-form' ),
                'genericError'   => __( 'There was an error submitting your form. Please try again.', 'nbnu-expense-form' ),
                'selectedFiles'  => __( 'Selected Files:', 'nbnu-expense-form' ),
                'requiredField'  => __( 'This field is required.', 'nbnu-expense-form' ),
                'invalidEmail'   => __( 'Please enter a valid email', 'nbnu-expense-form' ),
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

    wp_update_post(
        [
            'ID'           => $post_id,
            'post_content' => wp_json_encode( $form_data ),
        ]
    );

    update_post_meta( $post_id, 'nbnu_submission_status', 'pending' );

    wp_send_json_success(
        [ 'message' => __( 'Form submitted successfully!', 'nbnu-expense-form' ) ]
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

    foreach ( $files['name'] as $index => $name ) {
        if ( empty( $name ) || UPLOAD_ERR_OK !== $files['error'][ $index ] ) {
            continue;
        }

        $file_array = [
            'name'     => sanitize_file_name( $name ),
            'type'     => $files['type'][ $index ],
            'tmp_name' => $files['tmp_name'][ $index ],
            'error'    => 0,
            'size'     => (int) $files['size'][ $index ],
        ];

        $overrides = [ 'test_form' => false ];
        $result    = wp_handle_upload( $file_array, $overrides );

        if ( isset( $result['error'] ) || empty( $result['file'] ) ) {
            continue;
        }

        $attachment = [
            'post_mime_type' => $result['type'],
            'post_title'     => sanitize_text_field( pathinfo( $file_array['name'], PATHINFO_FILENAME ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attachment_id = wp_insert_attachment( $attachment, $result['file'], $post_id );

        if ( ! is_wp_error( $attachment_id ) ) {
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $result['file'] );
            wp_update_attachment_metadata( $attachment_id, $attachment_data );
            $uploaded_files[] = wp_get_attachment_url( $attachment_id );
        }
    }

    return $uploaded_files;
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

    if ( 'submission' === $view && $id ) {
        $submission = nbnu_expense_get_submission( $id );

        if ( ! $submission ) {
            printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'Submission not found.', 'nbnu-expense-form' ) );
            return;
        }

        $form_data = $submission['form_data'];
        include plugin_dir_path( __FILE__ ) . 'includes/nbnu-form-admin-view.php';
        return;
    }

    $submissions = nbnu_expense_get_submissions();
    include plugin_dir_path( __FILE__ ) . 'includes/nbnu-form-admin-list.php';
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

    $status = get_post_meta( $post->ID, 'nbnu_submission_status', true );

    if ( empty( $status ) ) {
        $status = 'pending';
    }

    return [
        'id'               => $post->ID,
        'submission_date'  => get_date_from_gmt( $post->post_date_gmt, 'Y-m-d H:i:s' ),
        'status'           => $status,
        'form_data'        => $form_data,
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
    $map = [
        'pending'  => __( 'Pending', 'nbnu-expense-form' ),
        'approved' => __( 'Approved', 'nbnu-expense-form' ),
        'rejected' => __( 'Rejected', 'nbnu-expense-form' ),
    ];

    $status = strtolower( (string) $status );

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
