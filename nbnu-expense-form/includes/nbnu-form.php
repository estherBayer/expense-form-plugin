<?php
/**
 * NBNU Form Template
 * Template part for displaying the NBNU expense form
 */
?>

<div class="nbnu-form-container">
    <h1 class="nbnu-form-title">NBNU Expense Form</h1>
    
    <div class="nbnu-form-update-notice">
        Form Update: SINs will no longer be collected on this form.
    </div>

    <div class="nbnu-error-global nbnu-hidden" id="nbnu-global-error-message">
        You are missing a few required fields. Please review the form!
    </div>

    <div class="nbnu-success-message nbnu-hidden" id="nbnu-confirmation-message"></div>

    <form id="nbnu-expense-form" enctype="multipart/form-data">
        <!-- Personal Information Section -->
        <div class="nbnu-form-section">
            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_meeting">Meeting <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_meeting" name="form_meeting" class="nbnu-form-input" required>
                </div>
                <div class="nbnu-input-group">
                    <label for="form_dates">Dates <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_dates" name="form_dates" class="nbnu-form-input" required>
                </div>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_name">Name <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_name" name="form_name" class="nbnu-form-input" required>
                </div>
                <div class="nbnu-input-group">
                    <label>DOB <span class="nbnu-required">*</span></label>
                    <div class="nbnu-date-inputs">
                        <div class="nbnu-date-input-wrapper">
                            <select id="date_month" name="date_month" class="nbnu-form-select" required>
                                <option value="">-</option>
                                <?php for($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <label>Month</label>
                        </div>
                        <div class="nbnu-date-input-wrapper">
                            <select id="date_day" name="date_day" class="nbnu-form-select" required>
                                <option value="">-</option>
                                <?php for($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <label>Day</label>
                        </div>
                        <div class="nbnu-date-input-wrapper">
                            <select id="date_year" name="date_year" class="nbnu-form-select" required>
                                <option value="">-</option>
                                <?php for($i = 1940; $i <= 2010; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <label>Year</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nbnu-input-group">
                <label for="form_address">Address <span class="nbnu-required">*</span></label>
                <input type="text" id="form_address" name="form_address" class="nbnu-form-input" required>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_employer">Employer <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_employer" name="form_employer" class="nbnu-form-input" required>
                </div>
                <div class="nbnu-input-group">
                    <label for="form_classifications">Classification <span class="nbnu-required">*</span></label>
                    <select id="form_classifications" name="form_classifications" class="nbnu-form-select" required>
                        <option value="">Select Classification</option>
                        <option value="RNCA">RNCA</option>
                        <option value="RNCB">RNCB</option>
                        <option value="RNCC">RNCC</option>
                        <option value="RNCD">RNCD</option>
                        <option value="Nurse Manager">Nurse Manager</option>
                        <option value="Nurse Supervisor">Nurse Supervisor</option>
                        <option value="LPN">LPN</option>
                    </select>
                </div>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_hourly_rate">Hourly Rate <span class="nbnu-required">*</span></label>
                    <input type="text" id="form_hourly_rate" name="form_hourly_rate" class="nbnu-form-input" placeholder="$0.00" required>
                </div>
                <div class="nbnu-input-group">
                    <label>Was this meeting out of province? <span class="nbnu-required">*</span></label>
                    <div class="nbnu-radio-group">
                        <label><input type="radio" name="form_meeting_out_of_province" value="yes"> Yes</label>
                        <label><input type="radio" name="form_meeting_out_of_province" value="no" checked> No</label>
                    </div>
                </div>
            </div>

            <div class="nbnu-input-group">
                <label for="form_provincial_or_local_office">Are you submitting this form to Provincial Office or Local Office? <span class="nbnu-required">*</span></label>
                <select id="form_provincial_or_local_office" name="form_provincial_or_local_office" class="nbnu-form-select" required>
                    <option value="Provincial Office">Provincial Office</option>
                    <option value="Local Office">Local Office</option>
                </select>
            </div>

            <div class="nbnu-input-group nbnu-hidden" id="nbnu-local-office-email">
                <label for="form_provincial_or_local_office_email">Enter Your Local Office Email:</label>
                <input type="email" id="form_provincial_or_local_office_email" name="form_provincial_or_local_office_email" class="nbnu-form-input">
            </div>
        </div>

        <!-- Main Form Grid -->
        <div class="nbnu-form-grid">
            <!-- Headers -->
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">Section 1 Salary</div>
            <div class="nbnu-grid-item nbnu-grid-item-header">SUN</div>
            <div class="nbnu-grid-item nbnu-grid-item-header">MON</div>
            <div class="nbnu-grid-item nbnu-grid-item-header">TUE</div>
            <div class="nbnu-grid-item nbnu-grid-item-header">WED</div>
            <div class="nbnu-grid-item nbnu-grid-item-header">THU</div>
            <div class="nbnu-grid-item nbnu-grid-item-header">FRI</div>
            <div class="nbnu-grid-item nbnu-grid-item-header">SAT</div>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">For Office Use Only</div>

            <!-- Date Row -->
            <div class="nbnu-grid-item nbnu-grid-span-2"><strong>Date (use picker):</strong></div>
            <?php 
            $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            foreach($days as $day): 
            ?>
                <div class="nbnu-grid-item">
                    <input type="text" class="nbnu-form-input nbnu-date-picker" id="form_<?php echo $day; ?>_date" name="form_<?php echo $day; ?>_date">
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                Total hours of travel & meeting time<br>
                <div class="nbnu-calculation-display" id="form_calc_total_hours_travel_meeting">0.00</div>
            </div>

            <!-- Hours of Travel -->
            <div class="nbnu-grid-item nbnu-grid-span-2">Hours of travel</div>
            <?php foreach($days as $day): ?>
                <div class="nbnu-grid-item">
                    <input type="number" step="0.25" class="nbnu-form-input nbnu-travel-hours nbnu-hidden" id="form_<?php echo $day; ?>_hours_travel" name="form_<?php echo $day; ?>_hours_travel">
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                Less # of hours billed by employer total<br>
                <div class="nbnu-calculation-display" id="form_calc_Less_hours_billed_by_employer">0.00</div>
            </div>

            <!-- Hours of Meeting -->
            <div class="nbnu-grid-item nbnu-grid-span-2">Hours of meeting</div>
            <?php foreach($days as $day): ?>
                <div class="nbnu-grid-item">
                    <input type="number" step="0.25" class="nbnu-form-input nbnu-meeting-hours nbnu-hidden" id="form_<?php echo $day; ?>_hours_meeting" name="form_<?php echo $day; ?>_hours_meeting">
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                Hours paid<br>
                <div class="nbnu-calculation-display" id="form_calc_hours_paid">0.00</div>
            </div>

<!-- Billing Questions -->
<div class="nbnu-grid-item nbnu-grid-span-2">Is your employer billing NBNU?<br>If so select shift.</div>
<?php foreach($days as $day): ?>
    <div class="nbnu-grid-item">
        <div class="nbnu-checkbox-group nbnu-billing-section nbnu-hidden" data-day="<?php echo $day; ?>">
            <label><input type="radio" name="form_<?php echo $day; ?>_employer_billing_NBNU" value="2"> 2 hr</label>
            <label><input type="radio" name="form_<?php echo $day; ?>_employer_billing_NBNU" value="4"> 4 hr</label>
            <label><input type="radio" name="form_<?php echo $day; ?>_employer_billing_NBNU" value="8"> 8 hr</label>
            <label><input type="radio" name="form_<?php echo $day; ?>_employer_billing_NBNU" value="10"> 10hr</label>
            <label><input type="radio" name="form_<?php echo $day; ?>_employer_billing_NBNU" value="12"> 12hr</label>
            <label><input type="radio" name="form_<?php echo $day; ?>_employer_billing_NBNU" value="No"> No</label>
        </div>
    </div>
<?php endforeach; ?>
<div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                Final hours paid<br>
                <div class="nbnu-calculation-display" id="form_calc_final_hours_paid">$0.00</div>
            </div>
</div>
        <!-- END of Main Form Grid -->

        <!-- Day Off Questions - Separate Grid -->
        <div class="nbnu-form-grid nbnu-conditional-grid nbnu-day-off-grid" data-row-type="day-off">
            <div class="nbnu-grid-item nbnu-grid-span-2">Are you on a day off?</div>
            <?php foreach($days as $day): ?>
                <div class="nbnu-grid-item">
                    <div class="nbnu-radio-group nbnu-day-off-section" data-day="<?php echo $day; ?>">
                        <label><input type="radio" name="form_<?php echo $day; ?>_day_off" value="No"> No</label>
						<label><input type="radio" name="form_<?php echo $day; ?>_day_off" value="Yes"> Yes</label>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"></div>
        </div>

<!-- LTD/WHSCC Questions - Separate Grid -->
        <div class="nbnu-form-grid nbnu-conditional-grid nbnu-ltd-grid" data-row-type="ltd">
            <div class="nbnu-grid-item nbnu-grid-span-2">Are you on LTD or WHSCC?</div>
            <?php foreach($days as $day): ?>
                <div class="nbnu-grid-item">
                    <div class="nbnu-radio-group nbnu-ltd-section" data-day="<?php echo $day; ?>">
                        <label><input type="radio" name="form_<?php echo $day; ?>_LTD_or_WHSCC" value="Yes"> Yes</label>
                        <label><input type="radio" name="form_<?php echo $day; ?>_LTD_or_WHSCC" value="No"> No</label>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2"></div>
        </div>
 
    <!-- END of first nbnu-form-section -->

    <!-- Section 2 Mileage - This should be in its own form-section div -->
    <div class="nbnu-form-section">
        <div class="nbnu-section-title">Section 2 Mileage</div>
            
            <div class="nbnu-input-group">
                <label>Did you use your own car?</label>
                <div class="nbnu-radio-group">
                    <label><input type="radio" name="form_use_own_car" value="yes"> Yes</label>
                    <label><input type="radio" name="form_use_own_car" value="no" checked> No</label>
                </div>
            </div>

            <div class="nbnu-personal-info">
                <div class="nbnu-input-group">
                    <label for="form_travelled_from">Travelled from</label>
                    <input type="text" id="form_travelled_from" name="form_travelled_from" class="nbnu-form-input">
                </div>
                <div class="nbnu-input-group">
                    <label for="form_travelled_to">To</label>
                    <input type="text" id="form_travelled_to" name="form_travelled_to" class="nbnu-form-input">
                </div>
            </div>

            <div class="nbnu-input-group">
                <p><strong>KM Instructions</strong> - The dropdown mileage chart is only applicable if your destination is Fredericton. If your destination is not Fredericton, then please check this box to input km:</p>
                <label><input type="checkbox" id="form_travel_destination_fredericton" name="form_travel_destination_fredericton"> Manual KM Entry</label>
            </div>

            <div class="nbnu-form-grid">
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">Kms travelled using own vehicle</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">SUN</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">MON</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">TUE</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">WED</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">THU</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">FRI</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">SAT</div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">Total</div>

                <div class="nbnu-grid-item nbnu-grid-span-2">Distance/Round Trip</div>
                <?php foreach($days as $day): ?>
                    <div class="nbnu-grid-item">
                        <div class="nbnu-km-input-wrapper">
                            <select class="nbnu-form-select nbnu-km-dropdown" id="form_<?php echo $day; ?>_kms_own_vehicle" name="form_<?php echo $day; ?>_kms_own_vehicle">
                                <option value="0">Select Location</option>
                                <option value="40">40 KM - Oromocto to Fredericton</option>
                                <option value="128">128 KM - Saint John to Fredericton</option>
                                <option value="195">195 KM - Moncton to Fredericton</option>
                                <option value="186">186 KM - Miramichi to Fredericton</option>
                                <option value="124">124 KM - Woodstock to Fredericton</option>
                                <option value="289">289 KM - Edmundston to Fredericton</option>
                                <option value="269">269 KM - Bathurst to Fredericton</option>
                                <option value="405">405 KM - Campbellton to Fredericton</option>
                                <option value="223">223 KM - Albert County to Fredericton</option>
                                <option value="300">300 KM - Bertrand to Fredericton</option>
                                <option value="147">147 KM - Black's Harbour to Fredericton</option>
                                <option value="139">139 KM - Blackville to Fredericton</option>
                                <option value="246">246 KM - Bouctouche to Fredericton</option>
                                <option value="221">221 KM - Campobello to Fredericton</option>
                                <option value="232">232 KM - Cape Station to Fredericton</option>
                                <option value="298">298 KM - Caraquet to Fredericton</option>
                                <option value="336">336 KM - Charlo to Fredericton</option>
                                <option value="228">228 KM - Cocagne to Fredericton</option>
                                <option value="350">350 KM - Dalhousie to Fredericton</option>
                                <option value="100">100 KM - Doaktown to Fredericton</option>
                                <option value="191">191 KM - Elgin to Fredericton</option>
                                <option value="157">157 KM - Florenceville to Fredericton</option>
                                <option value="51">51 KM - Fredericton Junction to Fredericton</option>
                                <option value="48">48 KM - Geary to Fredericton</option>
                                <option value="227">227 KM - Grand Falls to Fredericton</option>
                                <option value="191">191 KM - Grand Manan to Fredericton</option>
                                <option value="142">142 KM - Hampton to Fredericton</option>
                                <option value="60">60 KM - Harvey Station to Fredericton</option>
                                <option value="226">226 KM - Hopewell Cape to Fredericton</option>
                                <option value="97">97 KM - McAdam to Fredericton</option>
                                <option value="67">67 KM - Minto to Fredericton</option>
                                <option value="114">114 KM - Norton to Fredericton</option>
                                <option value="193">193 KM - Perth Andover to Fredericton</option>
                                <option value="231">231 KM - Plaster Rock to Fredericton</option>
                                <option value="143">143 KM - Quispamsis to Fredericton</option>
                                <option value="187">187 KM - Riverview to Fredericton</option>
                                <option value="138">138 KM - Rothesay to Fredericton</option>
                                <option value="239">239 KM - Sackville to Fredericton</option>
                                <option value="144">144 KM - Saint Andrews to Fredericton</option>
                                <option value="140">140 KM - Saint George to Fredericton</option>
                                <option value="248">248 KM - Saint Leonard to Fredericton</option>
                                <option value="143">143 KM - Saint Stephen to Fredericton</option>
                                <option value="216">216 KM - Shediac to Fredericton</option>
                                <option value="46">46 KM - Sheffield to Fredericton</option>
                                <option value="296">296 KM - Shippagan to Fredericton</option>
                                <option value="57">57 KM - Stanley to Fredericton</option>
                                <option value="139">139 KM - Sussex to Fredericton</option>
                                <option value="262">262 KM - Tracadie to Fredericton</option>
                                <option value="130">130 KM - Waterville to Fredericton</option>
                                <option value="90">90 KM - Young's Cove to Fredericton</option>
                            </select>
                            <input type="number" class="nbnu-form-input nbnu-km-manual nbnu-hidden" id="form_<?php echo $day; ?>_kms_manual" name="form_<?php echo $day; ?>_kms_manual" placeholder="Enter KM">
                            <div class="nbnu-round-trip-wrapper">
                                <input type="checkbox" id="form_<?php echo $day; ?>_round_trip" name="form_<?php echo $day; ?>_round_trip">
                                <label for="form_<?php echo $day; ?>_round_trip">Round trip</label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_total_kms_using_own_vehicle">$0.00</div>
                </div>
            </div>
		</div>

        <!-- Section 3 Meals -->
        <div class="nbnu-form-section">
            <div class="nbnu-section-title">Section 3 Meals (no receipts required)</div>
            
            <div class="nbnu-form-grid">
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">Check each meal claimed</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">SUN</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">MON</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">TUE</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">WED</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">THU</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">FRI</div>
                <div class="nbnu-grid-item nbnu-grid-item-header">SAT</div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">Total</div>

                <div class="nbnu-grid-item nbnu-grid-span-2">Meals</div>
                <?php foreach($days as $day): ?>
                    <div class="nbnu-grid-item">
                        <div class="nbnu-meal-grid">
                            <div class="meal-label">B</div>
                            <div class="meal-label">L</div>
                            <div class="meal-label">S</div>
                            <input type="checkbox" class="nbnu-meal-breakfast" name="form_<?php echo $day; ?>_meal_breakfast">
                            <input type="checkbox" class="nbnu-meal-lunch" name="form_<?php echo $day; ?>_meal_lunch">
                            <input type="checkbox" class="nbnu-meal-supper" name="form_<?php echo $day; ?>_meal_supper">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_meals_total">$0.00</div>
                </div>
            </div>
        </div>

        <!-- Section 4 Receipts -->
        <div class="nbnu-form-section">
            <div class="nbnu-section-title">Section 4 Receipts required except for direct billing</div>
            
            <div class="nbnu-form-grid">
                <div class="nbnu-grid-item nbnu-grid-span-2">Hotel accommodations</div>
                <div class="nbnu-grid-item nbnu-grid-span-2">
                    Number of nights<br>
                    <input type="number" id="form_hotel_number_nights" name="form_hotel_number_nights" class="nbnu-form-input" placeholder="0">
                </div>
                <div class="nbnu-grid-item nbnu-grid-span-2">
                    Night Rate<br>
                    <input type="number" step="0.01" id="form_hotel_night_rates" name="form_hotel_night_rates" class="nbnu-form-input" placeholder="0.00">
                </div>
                <div class="nbnu-grid-item nbnu-grid-span-3"></div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_hotels_acc_total">$0.00</div>
                </div>

                <div class="nbnu-grid-item nbnu-grid-span-2">If private accommodation</div>
                <div class="nbnu-grid-item nbnu-grid-span-2">
                    Number of nights<br>
                    <input type="number" id="form_private_acc_number_nights" name="form_private_acc_number_nights" class="nbnu-form-input" placeholder="0">
                </div>
                <div class="nbnu-grid-item nbnu-grid-span-5"></div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_private_acc_total">$0.00</div>
                </div>

                <div class="nbnu-grid-item nbnu-grid-span-2">Other</div>
                <div class="nbnu-grid-item nbnu-grid-span-2">
                    Parking, taxi, etc<br>
                    <input type="number" step="0.01" id="form_parking_taxi_etc" name="form_parking_taxi_etc" class="nbnu-form-input" placeholder="0.00">
                </div>
                <div class="nbnu-grid-item nbnu-grid-span-5"></div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_others_total">$0.00</div>
                </div>

                <div class="nbnu-grid-item nbnu-grid-span-9 nbnu-grid-item-header" style="text-align: right;">TOTAL SALARY AND EXPENSES PAID</div>
                <div class="nbnu-grid-item nbnu-grid-item-header nbnu-grid-span-2">
                    <div class="nbnu-calculation-display" id="form_calc_total_salary_expense_paid">$0.00</div>
                </div>
            </div>
        </div>

        <!-- File Upload Section -->
        <div class="nbnu-file-upload-section">
            <label for="form-files"><strong>Choose files:</strong></label>
            <input type="file" id="form-files" name="form_files[]" accept="image/*,application/pdf" multiple>
            <div id="nbnu-uploaded-files-preview"></div>
        </div>

        <!-- Comments Section -->
        <div class="nbnu-comments-section">
            <label>
                <input type="checkbox" id="wants-to-submit-content" name="wants_to_submit_content">
                Add Comments
            </label>
            <div id="nbnu-show-hide-comments" class="nbnu-hidden">
                <label for="form_comments"><strong>Comments</strong></label>
                <textarea id="form_comments" name="form_comments" rows="6" placeholder="Enter your comments here..."></textarea>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="nbnu-submit-section">
            <div class="nbnu-loading-spinner" id="nbnu-spinning-icon-confirmation"></div>
            <button type="submit" class="nbnu-submit-button" id="nbnu-form-submit">Submit</button>
        </div>
    </form>
</div>