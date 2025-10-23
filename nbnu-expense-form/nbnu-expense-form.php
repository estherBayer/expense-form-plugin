<?php
/*
Plugin Name: NBNU Expense Form
Description: Standalone bilingual expense form plugin (WPML compatible) with admin editing interface.
Version: 1.0
Author: NBNU
*/

if (!defined('ABSPATH')) exit;

// Enqueue assets
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('nbnu-expense-form', plugin_dir_url(__FILE__) . 'assets/nbnu-form.css', [], '1.0');
    wp_enqueue_script('nbnu-expense-form', plugin_dir_url(__FILE__) . 'assets/nbnu-form.js', ['jquery', 'jquery-ui-datepicker'], '1.0', true);
    wp_localize_script('nbnu-expense-form', 'nbnu_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('nbnu_form_nonce')
    ]);
});

// Shortcode
add_shortcode('nbnu_expense_form', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'includes/nbnu-form.php';
    return ob_get_clean();
});

// AJAX submission
add_action('wp_ajax_submit_nbnu_form', 'nbnu_handle_form_submit');
add_action('wp_ajax_nopriv_submit_nbnu_form', 'nbnu_handle_form_submit');

function nbnu_handle_form_submit() {
    check_ajax_referer('nbnu_form_nonce', 'nonce');
    $form_data = [];
    foreach ($_POST as $key => $value) {
        $form_data[$key] = sanitize_text_field($value);
    }
    $post_id = wp_insert_post([
        'post_type' => 'nbnu_expense',
        'post_title' => $form_data['form_name'] ?? 'Expense Submission',
        'post_content' => wp_json_encode($form_data),
        'post_status' => 'private'
    ]);
    if ($post_id) {
        wp_send_json_success(['message' => __('Form submitted successfully!', 'nbnu-expense-form')]);
    } else {
        wp_send_json_error(['message' => __('Error saving form. Please try again.', 'nbnu-expense-form')]);
    }
}

// CPT
add_action('init', function () {
    register_post_type('nbnu_expense', [
        'labels' => [
            'name' => __('Expense Forms', 'nbnu-expense-form'),
            'singular_name' => __('Expense Form', 'nbnu-expense-form')
        ],
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-clipboard'
    ]);
});

// Admin page
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=nbnu_expense',
        __('NBNU Form Submissions', 'nbnu-expense-form'),
        __('Admin View', 'nbnu-expense-form'),
        'manage_options',
        'nbnu-expense-admin',
        function () {
            include plugin_dir_path(__FILE__) . 'includes/nbnu-form-admin-list.php';
        }
    );
});

// Load translations
add_action('plugins_loaded', function () {
    load_plugin_textdomain('nbnu-expense-form', false, dirname(plugin_basename(__FILE__)) . '/languages');
});
