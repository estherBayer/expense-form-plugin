<?php
/**
 * NBNU Form Template
 * Template part for displaying the NBNU expense form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$nbnu_form_context      = isset( $nbnu_form_context ) ? $nbnu_form_context : 'public';
$nbnu_existing_data     = isset( $nbnu_existing_data ) && is_array( $nbnu_existing_data ) ? $nbnu_existing_data : [];
$nbnu_submission_id     = isset( $nbnu_submission_id ) ? (int) $nbnu_submission_id : 0;
$nbnu_submission_status = isset( $nbnu_submission_status ) ? $nbnu_submission_status : 'pending';
$nbnu_submission_token  = isset( $nbnu_submission_token ) ? $nbnu_submission_token : '';
$nbnu_submission_number = isset( $nbnu_submission_number ) ? $nbnu_submission_number : '';
$nbnu_staff_notes       = isset( $nbnu_staff_notes ) ? $nbnu_staff_notes : '';
$nbnu_form_action       = 'admin' === $nbnu_form_context ? admin_url( 'admin-post.php' ) : '';

$day_keys = [ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' ];
$day_labels = [
    'sun' => __( 'SUN', 'nbnu-expense-form' ),
    'mon' => __( 'MON', 'nbnu-expense-form' ),
    'tue' => __( 'TUE', 'nbnu-expense-form' ),
    'wed' => __( 'WED', 'nbnu-expense-form' ),
    'thu' => __( 'THU', 'nbnu-expense-form' ),
    'fri' => __( 'FRI', 'nbnu-expense-form' ),
    'sat' => __( 'SAT', 'nbnu-expense-form' ),
];
$day_names = [
    'sun' => __( 'Sunday', 'nbnu-expense-form' ),
    'mon' => __( 'Monday', 'nbnu-expense-form' ),
    'tue' => __( 'Tuesday', 'nbnu-expense-form' ),
    'wed' => __( 'Wednesday', 'nbnu-expense-form' ),
    'thu' => __( 'Thursday', 'nbnu-expense-form' ),
    'fri' => __( 'Friday', 'nbnu-expense-form' ),
    'sat' => __( 'Saturday', 'nbnu-expense-form' ),
];

$classification_options = [
    'RNCA',
    'RNCB',
    'RNCC',
    'RNCD',
    __( 'Nurse Manager', 'nbnu-expense-form' ),
    __( 'Nurse Supervisor', 'nbnu-expense-form' ),
    'LPN',
];

$km_options = [
    [
        'value' => '0',
        'label' => __( 'Select Location', 'nbnu-expense-form' ),
    ],
    [
        'value' => '40',
        'label' => __( '40 KM - Oromocto to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '128',
        'label' => __( '128 KM - Saint John to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '195',
        'label' => __( '195 KM - Moncton to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '186',
        'label' => __( '186 KM - Miramichi to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '124',
        'label' => __( '124 KM - Woodstock to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '289',
        'label' => __( '289 KM - Edmundston to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '269',
        'label' => __( '269 KM - Bathurst to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '405',
        'label' => __( '405 KM - Campbellton to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '223',
        'label' => __( '223 KM - Albert County to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '300',
        'label' => __( '300 KM - Bertrand to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '147',
        'label' => __( "147 KM - Black's Harbour to Fredericton", 'nbnu-expense-form' ),
    ],
    [
        'value' => '139',
        'label' => __( "139 KM - Blackville to Fredericton", 'nbnu-expense-form' ),
    ],
    [
        'value' => '246',
        'label' => __( '246 KM - Bouctouche to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '221',
        'label' => __( '221 KM - Campobello to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '232',
        'label' => __( '232 KM - Cape Station to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '298',
        'label' => __( '298 KM - Caraquet to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '336',
        'label' => __( '336 KM - Charlo to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '228',
        'label' => __( '228 KM - Cocagne to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '350',
        'label' => __( '350 KM - Dalhousie to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '100',
        'label' => __( '100 KM - Doaktown to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '191',
        'label' => __( '191 KM - Elgin to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '157',
        'label' => __( '157 KM - Florenceville to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '51',
        'label' => __( '51 KM - Fredericton Junction to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '48',
        'label' => __( '48 KM - Geary to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '227',
        'label' => __( '227 KM - Grand Falls to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '191',
        'label' => __( '191 KM - Grand Manan to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '142',
        'label' => __( '142 KM - Hampton to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '60',
        'label' => __( '60 KM - Harvey Station to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '226',
        'label' => __( '226 KM - Hopewell Cape to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '97',
        'label' => __( '97 KM - McAdam to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '67',
        'label' => __( '67 KM - Minto to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '114',
        'label' => __( '114 KM - Norton to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '193',
        'label' => __( '193 KM - Perth Andover to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '231',
        'label' => __( '231 KM - Plaster Rock to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '143',
        'label' => __( '143 KM - Quispamsis to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '187',
        'label' => __( '187 KM - Riverview to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '138',
        'label' => __( '138 KM - Rothesay to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '239',
        'label' => __( '239 KM - Sackville to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '144',
        'label' => __( '144 KM - Saint Andrews to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '140',
        'label' => __( '140 KM - Saint George to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '248',
        'label' => __( '248 KM - Saint Leonard to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '143',
        'label' => __( '143 KM - Saint Stephen to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '216',
        'label' => __( '216 KM - Shediac to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '46',
        'label' => __( '46 KM - Sheffield to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '296',
        'label' => __( '296 KM - Shippagan to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '57',
        'label' => __( '57 KM - Stanley to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '139',
        'label' => __( '139 KM - Sussex to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '262',
        'label' => __( '262 KM - Tracadie to Fredericton', 'nbnu-expense-form' ),
    ],
    [
        'value' => '130',
        'label' => __( "130 KM - Waterville to Fredericton", 'nbnu-expense-form' ),
    ],
    [
        'value' => '90',
        'label' => __( "90 KM - Young's Cove to Fredericton", 'nbnu-expense-form' ),
    ],
];
?>

<div class="nbnu-form-container">
    <h1 class="nbnu-form-title"><?php esc_html_e( 'NBNU Expense Form', 'nbnu-expense-form' ); ?></h1>

    <div class="nbnu-form-update-notice">
        <?php esc_html_e( 'Form Update: SINs will no longer be collected on this form.', 'nbnu-expense-form' ); ?>
    </div>

    <div class="nbnu-error-global nbnu-hidden" id="nbnu-global-error-message">
        <?php esc_html_e( 'You are missing a few required fields. Please review the form!', 'nbnu-expense-form' ); ?>
    </div>

    <div class="nbnu-success-message nbnu-hidden" id="nbnu-confirmation-message"></div>

    <form id="nbnu-expense-form" method="post" action="<?php echo esc_url( $nbnu_form_action ); ?>" enctype="multipart/form-data" data-context="<?php echo esc_attr( $nbnu_form_context ); ?>">
        <?php if ( 'admin' === $nbnu_form_context ) : ?>
            <?php wp_nonce_field( 'nbnu_expense_update_submission' ); ?>
            <input type="hidden" name="action" value="nbnu_expense_update_submission">
            <input type="hidden" name="submission_id" value="<?php echo (int) $nbnu_submission_id; ?>">
            <input type="hidden" name="nbnu_submission_token" value="<?php echo esc_attr( $nbnu_submission_token ); ?>">
        <?php endif; ?>
        <div class="nbnu-form-section">
            <div class="nbnu-form-section-header">
                <h2><?php esc_html_e( 'Personal Information', 'nbnu-expense-form' ); ?></h2>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_meeting"><?php esc_html_e( 'Meeting', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_meeting" name="form_meeting" class="nbnu-form-input" required>
                </div>
                <div class="nbnu-input-group">
                    <label for="form_dates"><?php esc_html_e( 'Dates', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_dates" name="form_dates" class="nbnu-form-input" required>
                </div>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_name"><?php esc_html_e( 'Name', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_name" name="form_name" class="nbnu-form-input" required>
                </div>
                <div class="nbnu-input-group">
                    <label><?php esc_html_e( 'DOB', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <div class="nbnu-date-inputs">
                        <div class="nbnu-date-input-wrapper">
                            <select id="date_month" name="date_month" class="nbnu-form-select" required>
                                <option value="">-</option>
                                <?php for ( $i = 1; $i <= 12; $i++ ) : ?>
                                    <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
                                <?php endfor; ?>
                            </select>
                            <label><?php esc_html_e( 'Month', 'nbnu-expense-form' ); ?></label>
                        </div>
                        <div class="nbnu-date-input-wrapper">
                            <select id="date_day" name="date_day" class="nbnu-form-select" required>
                                <option value="">-</option>
                                <?php for ( $i = 1; $i <= 31; $i++ ) : ?>
                                    <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
                                <?php endfor; ?>
                            </select>
                            <label><?php esc_html_e( 'Day', 'nbnu-expense-form' ); ?></label>
                        </div>
                        <div class="nbnu-date-input-wrapper">
                            <select id="date_year" name="date_year" class="nbnu-form-select" required>
                                <option value="">-</option>
                                <?php for ( $i = 1940; $i <= 2010; $i++ ) : ?>
                                    <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
                                <?php endfor; ?>
                            </select>
                            <label><?php esc_html_e( 'Year', 'nbnu-expense-form' ); ?></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nbnu-input-group">
                <label for="form_member_email"><?php esc_html_e( 'Email', 'nbnu-expense-form' ); ?></label>
                <input type="email" id="form_member_email" name="form_member_email" class="nbnu-form-input">
            </div>

            <div class="nbnu-input-group">
                <label for="form_address"><?php esc_html_e( 'Address', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                <input type="text" id="form_address" name="form_address" class="nbnu-form-input" required>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_employer"><?php esc_html_e( 'Employer', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_employer" name="form_employer" class="nbnu-form-input" required>
                </div>
                <div class="nbnu-input-group">
                    <label for="form_classifications"><?php esc_html_e( 'Classification', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <select id="form_classifications" name="form_classifications" class="nbnu-form-select" required>
                        <option value=""><?php esc_html_e( 'Select Classification', 'nbnu-expense-form' ); ?></option>
                        <?php foreach ( $classification_options as $option ) : ?>
                            <option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_hourly_rate"><?php esc_html_e( 'Hourly Rate', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_hourly_rate" name="form_hourly_rate" class="nbnu-form-input nbnu-numeric-input" data-numeric-currency="1" placeholder="<?php echo esc_attr__( '$0.00', 'nbnu-expense-form' ); ?>" required>
                </div>
                <div class="nbnu-input-group">
                    <label><?php esc_html_e( 'Was this meeting out of province?', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                    <div class="nbnu-radio-group">
                        <label><input type="radio" name="form_meeting_out_of_province" value="yes"> <?php esc_html_e( 'Yes', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_meeting_out_of_province" value="no" checked> <?php esc_html_e( 'No', 'nbnu-expense-form' ); ?></label>
                    </div>
                </div>
            </div>

            <div class="nbnu-input-group">
                <label for="form_provincial_or_local_office"><?php esc_html_e( 'Are you submitting this form to Provincial Office or Local Office?', 'nbnu-expense-form' ); ?> <span class="nbnu-required">*</span></label>
                <select id="form_provincial_or_local_office" name="form_provincial_or_local_office" class="nbnu-form-select" required>
                    <option value="Provincial Office"><?php esc_html_e( 'Provincial Office', 'nbnu-expense-form' ); ?></option>
                    <option value="Local Office"><?php esc_html_e( 'Local Office', 'nbnu-expense-form' ); ?></option>
                </select>
            </div>

            <div class="nbnu-input-group nbnu-hidden" id="nbnu-local-office-email">
                <label for="form_provincial_or_local_office_email"><?php esc_html_e( 'Enter Your Local Office Email:', 'nbnu-expense-form' ); ?></label>
                <input type="email" id="form_provincial_or_local_office_email" name="form_provincial_or_local_office_email" class="nbnu-form-input">
            </div>
        </div>

        <div class="nbnu-form-grid">
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"><?php esc_html_e( 'Section 1 Salary', 'nbnu-expense-form' ); ?></div>
            <?php foreach ( $day_keys as $day_key ) : ?>
                <div class="nbnu-grid-item nbnu-grid-item-header"><?php echo esc_html( $day_labels[ $day_key ] ); ?></div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"><?php esc_html_e( 'For Office Use Only', 'nbnu-expense-form' ); ?></div>

            <div class="nbnu-grid-item nbnu-grid-span-2"><strong><?php esc_html_e( 'Date (use picker):', 'nbnu-expense-form' ); ?></strong></div>
            <?php foreach ( $day_keys as $day_key ) : ?>
                <div class="nbnu-grid-item">
                    <input type="text" class="nbnu-form-input nbnu-date-picker" id="form_<?php echo esc_attr( $day_key ); ?>_date" name="form_<?php echo esc_attr( $day_key ); ?>_date">
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                <?php esc_html_e( 'Total hours of travel & meeting time', 'nbnu-expense-form' ); ?><br>
                <div class="nbnu-calculation-display" id="form_calc_total_hours_travel_meeting">0.00</div>
            </div>

            <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Hours of travel', 'nbnu-expense-form' ); ?></div>
            <?php foreach ( $day_keys as $day_key ) : ?>
                <div class="nbnu-grid-item">
                    <input type="number" step="0.25" class="nbnu-form-input nbnu-travel-hours nbnu-hidden nbnu-numeric-input" id="form_<?php echo esc_attr( $day_key ); ?>_hours_travel" name="form_<?php echo esc_attr( $day_key ); ?>_hours_travel">
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                <?php esc_html_e( 'Less # of hours billed by employer total', 'nbnu-expense-form' ); ?><br>
                <div class="nbnu-calculation-display" id="form_calc_Less_hours_billed_by_employer">0.00</div>
            </div>

            <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Hours of meeting', 'nbnu-expense-form' ); ?></div>
            <?php foreach ( $day_keys as $day_key ) : ?>
                <div class="nbnu-grid-item">
                    <input type="number" step="0.25" class="nbnu-form-input nbnu-meeting-hours nbnu-hidden nbnu-numeric-input" id="form_<?php echo esc_attr( $day_key ); ?>_hours_meeting" name="form_<?php echo esc_attr( $day_key ); ?>_hours_meeting">
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                <?php esc_html_e( 'Hours paid', 'nbnu-expense-form' ); ?><br>
                <div class="nbnu-calculation-display" id="form_calc_hours_paid">0.00</div>
            </div>

            <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Is your employer billing NBNU? If so select shift.', 'nbnu-expense-form' ); ?></div>
            <?php foreach ( $day_keys as $day_key ) : ?>
                <div class="nbnu-grid-item">
                    <div class="nbnu-checkbox-group nbnu-billing-section nbnu-hidden" data-day="<?php echo esc_attr( $day_key ); ?>">
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_employer_billing_NBNU" value="2"> <?php esc_html_e( '2 hr', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_employer_billing_NBNU" value="4"> <?php esc_html_e( '4 hr', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_employer_billing_NBNU" value="8"> <?php esc_html_e( '8 hr', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_employer_billing_NBNU" value="10"> <?php esc_html_e( '10 hr', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_employer_billing_NBNU" value="12"> <?php esc_html_e( '12 hr', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_employer_billing_NBNU" value="No"> <?php esc_html_e( 'No', 'nbnu-expense-form' ); ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                <?php esc_html_e( 'Final hours paid', 'nbnu-expense-form' ); ?><br>
                <div class="nbnu-calculation-display" id="form_calc_final_hours_paid">$0.00</div>
            </div>
        </div>

        <div class="nbnu-form-grid nbnu-conditional-grid nbnu-day-off-grid" data-row-type="day-off">
            <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Are you on a day off?', 'nbnu-expense-form' ); ?></div>
            <?php foreach ( $day_keys as $day_key ) : ?>
                <div class="nbnu-grid-item">
                    <div class="nbnu-radio-group nbnu-day-off-section" data-day="<?php echo esc_attr( $day_key ); ?>">
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_day_off" value="No"> <?php esc_html_e( 'No', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_day_off" value="Yes"> <?php esc_html_e( 'Yes', 'nbnu-expense-form' ); ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"></div>
        </div>

        <div class="nbnu-form-grid nbnu-conditional-grid nbnu-ltd-grid" data-row-type="ltd">
            <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Are you on LTD or WHSCC?', 'nbnu-expense-form' ); ?></div>
            <?php foreach ( $day_keys as $day_key ) : ?>
                <div class="nbnu-grid-item">
                    <div class="nbnu-radio-group nbnu-ltd-section" data-day="<?php echo esc_attr( $day_key ); ?>">
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_LTD_or_WHSCC" value="Yes"> <?php esc_html_e( 'Yes', 'nbnu-expense-form' ); ?></label>
                        <label><input type="radio" name="form_<?php echo esc_attr( $day_key ); ?>_LTD_or_WHSCC" value="No"> <?php esc_html_e( 'No', 'nbnu-expense-form' ); ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"></div>
        </div>

        <div class="nbnu-form-section">
            <div class="nbnu-section-title"><?php esc_html_e( 'Section 2 Mileage', 'nbnu-expense-form' ); ?></div>

            <div class="nbnu-input-group">
                <label><?php esc_html_e( 'Did you use your own car?', 'nbnu-expense-form' ); ?></label>
                <div class="nbnu-radio-group">
                    <label><input type="radio" name="form_use_own_car" value="yes"> <?php esc_html_e( 'Yes', 'nbnu-expense-form' ); ?></label>
                    <label><input type="radio" name="form_use_own_car" value="no" checked> <?php esc_html_e( 'No', 'nbnu-expense-form' ); ?></label>
                </div>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_travelled_from"><?php esc_html_e( 'Travelled from', 'nbnu-expense-form' ); ?></label>
                    <input type="text" id="form_travelled_from" name="form_travelled_from" class="nbnu-form-input">
                </div>
                <div class="nbnu-input-group">
                    <label for="form_travelled_to"><?php esc_html_e( 'To', 'nbnu-expense-form' ); ?></label>
                    <input type="text" id="form_travelled_to" name="form_travelled_to" class="nbnu-form-input">
                </div>
            </div>

            <div class="nbnu-input-group">
                <p><strong><?php esc_html_e( 'KM Instructions', 'nbnu-expense-form' ); ?></strong> - <?php esc_html_e( 'The dropdown mileage chart is only applicable if your destination is Fredericton. If your destination is not Fredericton, then please check this box to input km:', 'nbnu-expense-form' ); ?></p>
                <label><input type="checkbox" id="form_travel_destination_fredericton" name="form_travel_destination_fredericton"> <?php esc_html_e( 'Manual KM Entry', 'nbnu-expense-form' ); ?></label>
            </div>

            <div class="nbnu-form-grid">
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"><?php esc_html_e( 'Kms travelled using own vehicle', 'nbnu-expense-form' ); ?></div>
                <?php foreach ( $day_keys as $day_key ) : ?>
                    <div class="nbnu-grid-item nbnu-grid-item-header"><?php echo esc_html( $day_labels[ $day_key ] ); ?></div>
                <?php endforeach; ?>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"><?php esc_html_e( 'Total', 'nbnu-expense-form' ); ?></div>

                <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Distance/Round Trip', 'nbnu-expense-form' ); ?></div>
                <?php foreach ( $day_keys as $day_key ) : ?>
                    <div class="nbnu-grid-item">
                        <div class="nbnu-km-input-wrapper">
                            <select class="nbnu-form-select nbnu-km-dropdown" id="form_<?php echo esc_attr( $day_key ); ?>_kms_own_vehicle" name="form_<?php echo esc_attr( $day_key ); ?>_kms_own_vehicle">
                                <?php foreach ( $km_options as $km_option ) : ?>
                                    <option value="<?php echo esc_attr( $km_option['value'] ); ?>"><?php echo esc_html( $km_option['label'] ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" class="nbnu-form-input nbnu-km-manual nbnu-hidden nbnu-numeric-input" id="form_<?php echo esc_attr( $day_key ); ?>_kms_manual" name="form_<?php echo esc_attr( $day_key ); ?>_kms_manual" placeholder="<?php esc_attr_e( 'Enter KM', 'nbnu-expense-form' ); ?>">
                            <div class="nbnu-round-trip-wrapper">
                                <input type="checkbox" id="form_<?php echo esc_attr( $day_key ); ?>_round_trip" name="form_<?php echo esc_attr( $day_key ); ?>_round_trip">
                                <label for="form_<?php echo esc_attr( $day_key ); ?>_round_trip"><?php esc_html_e( 'Round trip', 'nbnu-expense-form' ); ?></label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_total_kms_using_own_vehicle">$0.00</div>
                </div>
            </div>
        </div>

        <div class="nbnu-form-section">
            <div class="nbnu-section-title"><?php esc_html_e( 'Section 3 Meals (no receipts required)', 'nbnu-expense-form' ); ?></div>

            <div class="nbnu-form-grid">
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"><?php esc_html_e( 'Check each meal claimed', 'nbnu-expense-form' ); ?></div>
                <?php foreach ( $day_keys as $day_key ) : ?>
                    <div class="nbnu-grid-item nbnu-grid-item-header"><?php echo esc_html( $day_labels[ $day_key ] ); ?></div>
                <?php endforeach; ?>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"><?php esc_html_e( 'Total', 'nbnu-expense-form' ); ?></div>

                <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Meals', 'nbnu-expense-form' ); ?></div>
                <?php foreach ( $day_keys as $day_key ) : ?>
                    <div class="nbnu-grid-item">
                        <div class="nbnu-meal-grid">
                            <div class="meal-label"><?php esc_html_e( 'B', 'nbnu-expense-form' ); ?></div>
                            <div class="meal-label"><?php esc_html_e( 'L', 'nbnu-expense-form' ); ?></div>
                            <div class="meal-label"><?php esc_html_e( 'S', 'nbnu-expense-form' ); ?></div>
                            <input type="checkbox" class="nbnu-meal-breakfast" name="form_<?php echo esc_attr( $day_key ); ?>_meal_breakfast">
                            <input type="checkbox" class="nbnu-meal-lunch" name="form_<?php echo esc_attr( $day_key ); ?>_meal_lunch">
                            <input type="checkbox" class="nbnu-meal-supper" name="form_<?php echo esc_attr( $day_key ); ?>_meal_supper">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_meals_total">$0.00</div>
                </div>
            </div>
        </div>

        <div class="nbnu-form-section">
            <div class="nbnu-section-title"><?php esc_html_e( 'Section 4 Receipts required except for direct billing', 'nbnu-expense-form' ); ?></div>

            <div class="nbnu-form-grid">
                <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Hotel accommodations', 'nbnu-expense-form' ); ?></div>
                <div class="nbnu-grid-item nbnu-grid-span-2">
                    <?php esc_html_e( 'Number of nights', 'nbnu-expense-form' ); ?><br>
                    <input type="number" id="form_hotel_number_nights" name="form_hotel_number_nights" class="nbnu-form-input nbnu-numeric-input" placeholder="0">
                </div>
                <div class="nbnu-grid-item nbnu-grid-span-2">
                    <?php esc_html_e( 'Night Rate', 'nbnu-expense-form' ); ?><br>
                    <input type="number" step="0.01" id="form_hotel_night_rates" name="form_hotel_night_rates" class="nbnu-form-input nbnu-numeric-input" placeholder="0.00">
                </div>
                <div class="nbnu-grid-item nbnu-grid-span-3"></div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_hotels_acc_total">$0.00</div>
                </div>

                <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'If private accommodation', 'nbnu-expense-form' ); ?></div>
                <div class="nbnu-grid-item nbnu-grid-span-2">
                    <?php esc_html_e( 'Number of nights', 'nbnu-expense-form' ); ?><br>
                    <input type="number" id="form_private_acc_number_nights" name="form_private_acc_number_nights" class="nbnu-form-input nbnu-numeric-input" placeholder="0">
                </div>
                <div class="nbnu-grid-item nbnu-grid-span-5"></div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_private_acc_total">$0.00</div>
                </div>

                <div class="nbnu-grid-item nbnu-grid-span-2"><?php esc_html_e( 'Parking/Taxi/etc', 'nbnu-expense-form' ); ?></div>
                <div class="nbnu-grid-item nbnu-grid-span-6">
                    <input type="number" step="0.01" id="form_parking_taxi_etc" name="form_parking_taxi_etc" class="nbnu-form-input nbnu-numeric-input" placeholder="0.00">
                </div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_others_total">$0.00</div>
                </div>
            </div>
        </div>

        <div class="nbnu-form-section">
            <div class="nbnu-section-title"><?php esc_html_e( 'Section 5 Total Salary & Expense Paid', 'nbnu-expense-form' ); ?></div>
            <div class="nbnu-form-grid">
                <div class="nbnu-grid-item nbnu-grid-span-8">
                    <strong><?php esc_html_e( 'Total salary & expenses paid', 'nbnu-expense-form' ); ?></strong>
                </div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_total_salary_expense_paid">$0.00</div>
                </div>
            </div>
        </div>

        <div class="nbnu-form-section">
            <div class="nbnu-section-title"><?php esc_html_e( 'Additional Comments', 'nbnu-expense-form' ); ?></div>
            <div class="nbnu-input-group">
                <label for="form_comments"><?php esc_html_e( 'Comments', 'nbnu-expense-form' ); ?></label>
                <textarea id="form_comments" name="form_comments" class="nbnu-form-textarea" rows="4"></textarea>
            </div>
        </div>

        <div class="nbnu-form-section">
            <div class="nbnu-section-title"><?php esc_html_e( 'File Uploads', 'nbnu-expense-form' ); ?></div>
            <div class="nbnu-input-group">
                <label for="form-files"><strong><?php esc_html_e( 'Choose files:', 'nbnu-expense-form' ); ?></strong></label>
                <input type="file" id="form-files" name="form_files[]" accept="image/*,application/pdf" multiple>
                <div id="nbnu-uploaded-files-preview"></div>
            </div>
        </div>

        <?php if ( 'admin' === $nbnu_form_context ) : ?>
            <div class="nbnu-form-section nbnu-admin-review">
                <div class="nbnu-section-title"><?php esc_html_e( 'Staff Review', 'nbnu-expense-form' ); ?></div>
                <div class="nbnu-input-group">
                    <label for="nbnu_submission_status"><?php esc_html_e( 'Submission Status', 'nbnu-expense-form' ); ?></label>
                    <select id="nbnu_submission_status" name="nbnu_submission_status" class="nbnu-form-select">
                        <?php foreach ( nbnu_expense_get_status_options() as $status_key => $status_label ) : ?>
                            <option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $nbnu_submission_status, $status_key ); ?>><?php echo esc_html( $status_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="nbnu-input-group">
                    <label for="nbnu_staff_notes"><?php esc_html_e( 'Staff Notes', 'nbnu-expense-form' ); ?></label>
                    <textarea id="nbnu_staff_notes" name="nbnu_staff_notes" class="nbnu-form-textarea" rows="4"><?php echo esc_textarea( $nbnu_staff_notes ); ?></textarea>
                </div>
            </div>
        <?php endif; ?>

        <div class="nbnu-form-actions">
            <button type="submit" id="nbnu-form-submit" class="nbnu-form-submit button button-primary"><?php echo 'admin' === $nbnu_form_context ? esc_html__( 'Update Submission', 'nbnu-expense-form' ) : esc_html__( 'Submit', 'nbnu-expense-form' ); ?></button>
            <div id="nbnu-spinning-icon-confirmation" class="nbnu-spinner" aria-hidden="true"></div>
        </div>

        <input type="hidden" name="form_calc_total_hours_travel_meeting" id="nbnu-total-hours-input" value="">
        <input type="hidden" name="form_calc_Less_hours_billed_by_employer" id="nbnu-total-hours-billed-input" value="">
        <input type="hidden" name="form_calc_hours_paid" id="nbnu-total-hours-paid-input" value="">
        <input type="hidden" name="form_calc_final_hours_paid" id="nbnu-total-pay-input" value="">
        <input type="hidden" name="form_calc_total_kms_using_own_vehicle" id="nbnu-total-kms-input" value="">
        <input type="hidden" name="form_calc_meals_total" id="nbnu-total-meals-input" value="">
        <input type="hidden" name="form_calc_hotels_acc_total" id="nbnu-total-hotel-input" value="">
        <input type="hidden" name="form_calc_private_acc_total" id="nbnu-total-private-input" value="">
        <input type="hidden" name="form_calc_others_total" id="nbnu-total-other-input" value="">
        <input type="hidden" name="form_calc_total_salary_expense_paid" id="nbnu-total-grand-input" value="">
    </form>
</div>
