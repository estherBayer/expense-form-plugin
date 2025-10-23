<?php
/**
 * NBNU Form Admin List Template
 * Displays all form submissions in admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e( 'NBNU Form Submissions', 'nbnu-expense-form' ); ?></h1>

    <?php if ( empty( $submissions ) ) : ?>
        <p><?php esc_html_e( 'No submissions found.', 'nbnu-expense-form' ); ?></p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'ID', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Name', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Meeting', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Submission Date', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Total Amount', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'nbnu-expense-form' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'nbnu-expense-form' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $submissions as $submission ) :
                    $form_data     = $submission['form_data'];
                    $submission_id = (int) $submission['id'];
                    $submission_date = ! empty( $submission['submission_date'] )
                        ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission['submission_date'] ) )
                        : '';
                    $total_amount = $form_data['form_calc_total_salary_expense_paid'] ?? '';
                    ?>
                    <tr>
                        <td><?php echo esc_html( $submission_id ); ?></td>
                        <td><?php echo esc_html( $form_data['form_name'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
                        <td><?php echo esc_html( $form_data['form_meeting'] ?? __( 'N/A', 'nbnu-expense-form' ) ); ?></td>
                        <td><?php echo esc_html( $submission_date ); ?></td>
                        <td><?php echo esc_html( $total_amount ?: __( '$0.00', 'nbnu-expense-form' ) ); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr( $submission['status'] ); ?>">
                                <?php echo esc_html( nbnu_expense_get_status_label( $submission['status'] ) ); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=nbnu-expense-admin&view=submission&id=' . $submission_id ) ); ?>"
                               class="button button-small"><?php esc_html_e( 'View Details', 'nbnu-expense-form' ); ?></a>
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
