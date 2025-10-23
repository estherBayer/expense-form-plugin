// NBNU Form JavaScript
jQuery(document).ready(function($) {
    // Constants for calculations
    const CONSTANTS = {
        rate_km: 0.52,
        rate_km_after_oct_2024: 0.58,
        base_breakfast_in_province: 15,
        base_lunch_in_province: 23,
        base_supper_in_province: 31,
        base_breakfast_out_province: 18,
        base_lunch_out_province: 28,
        base_supper_out_province: 48,
        priv_acc_per_night: 25,
        billingNBNUblockEquivalent: {
            2: 2,
            4: 3.75,
            8: 7.5,
            10: 8.33,
            12: 11.25
        }
    };

    const STRINGS = (typeof nbnu_ajax !== 'undefined' && nbnu_ajax.strings) ? nbnu_ajax.strings : {};

    let isOutOfProvince = false;

    // Initialize date pickers
    $('.nbnu-date-picker').datepicker({
        dateFormat: 'mm/dd/yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '2020:2030'
    });

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-CA', {
            style: 'currency',
            currency: 'CAD'
        }).format(amount || 0);
    }

    // Format number with two decimals
    function formatNumber(num) {
        return new Intl.NumberFormat('en-CA', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num || 0);
    }

    // Get km rate based on date
    function getKmRate(dateString) {
        if (!dateString) return CONSTANTS.rate_km;
        const date = new Date(dateString);
        const cutoffDate = new Date('10/19/2024');
        return date > cutoffDate ? CONSTANTS.rate_km_after_oct_2024 : CONSTANTS.rate_km;
    }

// Show/hide sections based on date input
function toggleDateSections() {
    const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    
    days.forEach(day => {
        const dateInput = $(`#form_${day}_date`);
        const hasDate = dateInput.val().trim() !== '';
        
        // Show/hide travel and meeting hours
        $(`#form_${day}_hours_travel, #form_${day}_hours_meeting`).toggleClass('nbnu-hidden', !hasDate);
        
        // Show/hide billing section only
        $(`.nbnu-billing-section[data-day="${day}"]`).toggleClass('nbnu-hidden', !hasDate);
        
        // Remove the code that was adding nbnu-hidden to day-off and ltd sections
        // We're handling their visibility at the grid level now
    });
}
    // Calculate total hours
    function calculateTotalHours() {
        let totalHours = 0;
        let billedHours = 0;

        const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        
        days.forEach(day => {
            const travelHours = parseFloat($(`#form_${day}_hours_travel`).val()) || 0;
            const meetingHours = parseFloat($(`#form_${day}_hours_meeting`).val()) || 0;
            const billingValue = $(`input[name="form_${day}_employer_billing_NBNU"]:checked`).val();
            const dayOff = $(`input[name="form_${day}_day_off"]:checked`).val();
            const ltdWhscc = $(`input[name="form_${day}_LTD_or_WHSCC"]:checked`).val();
            
            // Only count hours if not on LTD/WHSCC while employer not billing and on day off
            if (!(billingValue === 'No' && dayOff === 'No' && ltdWhscc === 'Yes')) {
                totalHours += travelHours + meetingHours;
            }
            
            // Calculate billed hours
            if (billingValue !== 'No') {
                billedHours += CONSTANTS.billingNBNUblockEquivalent[parseInt(billingValue)] || 0;
            }
        });

        const paidHours = Math.max(0, totalHours - billedHours);
        const hourlyRate = parseFloat($('#form_hourly_rate').val().replace(/[$,]/g, '')) || 0;
        const totalPay = paidHours * hourlyRate;

        $('#form_calc_total_hours_travel_meeting').text(formatNumber(totalHours));
        $('#form_calc_Less_hours_billed_by_employer').text(formatNumber(billedHours));
        $('#form_calc_hours_paid').text(formatNumber(paidHours));
        $('#form_calc_final_hours_paid').text(formatCurrency(totalPay));
        
        calculateGrandTotal();
    }

    // Calculate mileage
    function calculateMileage() {
        let totalMileageCost = 0;
        const useOwnCar = $('input[name="form_use_own_car"]:checked').val() === 'yes';
        
        if (useOwnCar) {
            const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            const manualEntry = $('#form_travel_destination_fredericton').is(':checked');
            
            days.forEach(day => {
                let km = 0;
                const dateValue = $(`#form_${day}_date`).val();
                const rate = getKmRate(dateValue);
                
                if (manualEntry) {
                    km = parseFloat($(`#form_${day}_kms_manual`).val()) || 0;
                } else {
                    km = parseFloat($(`#form_${day}_kms_own_vehicle`).val()) || 0;
                    const isRoundTrip = $(`#form_${day}_round_trip`).is(':checked');
                    if (isRoundTrip) km *= 2;
                }
                
                totalMileageCost += km * rate;
            });
        }
        
        $('#form_calc_total_kms_using_own_vehicle').text(formatCurrency(totalMileageCost));
        calculateGrandTotal();
    }

    // Calculate meals
    function calculateMeals() {
        let totalMeals = 0;
        const breakfastRate = isOutOfProvince ? CONSTANTS.base_breakfast_out_province : CONSTANTS.base_breakfast_in_province;
        const lunchRate = isOutOfProvince ? CONSTANTS.base_lunch_out_province : CONSTANTS.base_lunch_in_province;
        const supperRate = isOutOfProvince ? CONSTANTS.base_supper_out_province : CONSTANTS.base_supper_in_province;
        
        const breakfastCount = $('.nbnu-meal-breakfast:checked').length;
        const lunchCount = $('.nbnu-meal-lunch:checked').length;
        const supperCount = $('.nbnu-meal-supper:checked').length;
        
        totalMeals = (breakfastCount * breakfastRate) + (lunchCount * lunchRate) + (supperCount * supperRate);
        
        $('#form_calc_meals_total').text(formatCurrency(totalMeals));
        calculateGrandTotal();
    }

    // Calculate accommodation costs
    function calculateAccommodation() {
        // Hotel accommodation
        const hotelNights = parseFloat($('#form_hotel_number_nights').val()) || 0;
        const hotelRate = parseFloat($('#form_hotel_night_rates').val()) || 0;
        const hotelTotal = hotelNights * hotelRate;
        $('#form_calc_hotels_acc_total').text(formatCurrency(hotelTotal));
        
        // Private accommodation
        const privateNights = parseFloat($('#form_private_acc_number_nights').val()) || 0;
        const privateTotal = privateNights * CONSTANTS.priv_acc_per_night;
        $('#form_calc_private_acc_total').text(formatCurrency(privateTotal));
        
        // Other expenses
        const otherExpenses = parseFloat($('#form_parking_taxi_etc').val()) || 0;
        $('#form_calc_others_total').text(formatCurrency(otherExpenses));
        
        calculateGrandTotal();
    }

    // Calculate grand total
    function calculateGrandTotal() {
        const hoursPaid = parseFloat($('#form_calc_final_hours_paid').text().replace(/[$,]/g, '')) || 0;
        const mileage = parseFloat($('#form_calc_total_kms_using_own_vehicle').text().replace(/[$,]/g, '')) || 0;
        const meals = parseFloat($('#form_calc_meals_total').text().replace(/[$,]/g, '')) || 0;
        const hotel = parseFloat($('#form_calc_hotels_acc_total').text().replace(/[$,]/g, '')) || 0;
        const privateAcc = parseFloat($('#form_calc_private_acc_total').text().replace(/[$,]/g, '')) || 0;
        const other = parseFloat($('#form_calc_others_total').text().replace(/[$,]/g, '')) || 0;
        
        const grandTotal = hoursPaid + mileage + meals + hotel + privateAcc + other;
        $('#form_calc_total_salary_expense_paid').text(formatCurrency(grandTotal));
    }

// Event handlers
$('.nbnu-date-picker').on('change', function() {
    toggleDateSections();
    calculateTotalHours();
    calculateMileage();
    
    // Check if we need to show conditional rows based on current selections
    const day = $(this).attr('id').match(/form_(.+)_date/)[1];
    const billingValue = $(`input[name="form_${day}_employer_billing_NBNU"]:checked`).val();
    
    // Trigger the billing change event to update conditional visibility
    $(`input[name="form_${day}_employer_billing_NBNU"]:checked`).trigger('change');
});

    $('.nbnu-travel-hours, .nbnu-meeting-hours').on('input', calculateTotalHours);
$('input[name*="LTD_or_WHSCC"]').on('change', calculateTotalHours);
    $('#form_hourly_rate').on('input', calculateTotalHours);

    $('input[name="form_meeting_out_of_province"]').on('change', function() {
        isOutOfProvince = $(this).val() === 'yes';
        calculateMeals();
    });

    $('input[name="form_use_own_car"]').on('change', calculateMileage);
    $('#form_travel_destination_fredericton').on('change', function() {
        const manualEntry = $(this).is(':checked');
        $('.nbnu-km-dropdown').toggleClass('nbnu-hidden', manualEntry);
        $('.nbnu-km-manual').toggleClass('nbnu-hidden', !manualEntry);
        calculateMileage();
    });

    $('.nbnu-km-dropdown, .nbnu-km-manual, input[name*="round_trip"]').on('change input', calculateMileage);
    $('.nbnu-meal-breakfast, .nbnu-meal-lunch, .nbnu-meal-supper').on('change', calculateMeals);
    $('#form_hotel_number_nights, #form_hotel_night_rates, #form_private_acc_number_nights, #form_parking_taxi_etc').on('input', calculateAccommodation);

    $('#form_provincial_or_local_office').on('change', function() {
        if ($(this).val() === 'Local Office') {
            $('#nbnu-local-office-email').removeClass('nbnu-hidden');
            $('#form_provincial_or_local_office_email').attr('required', true);
        } else {
            $('#nbnu-local-office-email').addClass('nbnu-hidden');
            $('#form_provincial_or_local_office_email').removeAttr('required');
        }
    });

    $('#wants-to-submit-content').on('change', function() {
        $('#nbnu-show-hide-comments').toggleClass('nbnu-hidden', !this.checked);
    });

    // Salary formatting
    $('#form_hourly_rate').on('blur', function() {
        let value = $(this).val().replace(/[^0-9.]/g, '');
        if (value && !isNaN(value)) {
            $(this).val('$' + parseFloat(value).toFixed(2));
        }
    });

    // Form validation
    function validateForm() {
        $('.nbnu-form-validation-error, .nbnu-form-validation-error-select').removeClass('nbnu-form-validation-error nbnu-form-validation-error-select');
        
        let isValid = true;
        const requiredFields = [
            '#form_meeting', '#form_dates', '#form_name', '#form_address', 
            '#form_employer', '#form_hourly_rate', '#date_month', '#date_day', 
            '#date_year', '#form_classifications'
        ];
        
        requiredFields.forEach(field => {
            const $field = $(field);
            if (!$field.val() || !$field.val().trim()) {
                $field.addClass($field.is('select') ? 'nbnu-form-validation-error-select' : 'nbnu-form-validation-error');
                if (!$field.is('select')) {
                    $field.attr('placeholder', STRINGS.requiredField || 'This field is required.');
                }
                isValid = false;
            }
        });

        // Validate email if local office selected
        if ($('#form_provincial_or_local_office').val() === 'Local Office') {
            const email = $('#form_provincial_or_local_office_email').val();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailPattern.test(email)) {
                $('#form_provincial_or_local_office_email').addClass('nbnu-form-validation-error');
                $('#form_provincial_or_local_office_email').attr('placeholder', STRINGS.invalidEmail || 'Please enter a valid email');
                isValid = false;
            }
        }

        // Validate at least one date is entered
        const hasAnyDate = $('.nbnu-date-picker').toArray().some(input => $(input).val().trim() !== '');
        if (!hasAnyDate) {
            $('.nbnu-date-picker').addClass('nbnu-form-validation-error');
            isValid = false;
        }

        return isValid;
    }

    // Clear validation errors on focus
    $('input, select, textarea').on('focus', function() {
        $(this).removeClass('nbnu-form-validation-error nbnu-form-validation-error-select');
        const placeholdersToClear = [
            STRINGS.requiredField || 'This field is required.',
            STRINGS.invalidEmail || 'Please enter a valid email'
        ];
        if (placeholdersToClear.includes($(this).attr('placeholder'))) {
            $(this).removeAttr('placeholder');
        }
    });

    // Form submission
    $('#nbnu-expense-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            $('#nbnu-global-error-message').text(STRINGS.globalError || $('#nbnu-global-error-message').text()).removeClass('nbnu-hidden');
            $('html, body').animate({
                scrollTop: $('#nbnu-global-error-message').offset().top - 100
            }, 500);
            return;
        }

        $('#nbnu-global-error-message').addClass('nbnu-hidden');
        $('#nbnu-spinning-icon-confirmation').show();
        $('#nbnu-form-submit').prop('disabled', true).text(STRINGS.submitting || 'Submitting...');
        
        // Prepare form data
        const formData = new FormData(this);
        formData.append('action', 'submit_nbnu_form');
        formData.append('nonce', nbnu_ajax.nonce);
        
        // Add calculated values
        formData.append('form_calc_total_hours_travel_meeting', $('#form_calc_total_hours_travel_meeting').text());
        formData.append('form_calc_Less_hours_billed_by_employer', $('#form_calc_Less_hours_billed_by_employer').text());
        formData.append('form_calc_hours_paid', $('#form_calc_hours_paid').text());
        formData.append('form_calc_final_hours_paid', $('#form_calc_final_hours_paid').text());
        formData.append('form_calc_total_kms_using_own_vehicle', $('#form_calc_total_kms_using_own_vehicle').text());
        formData.append('form_calc_meals_total', $('#form_calc_meals_total').text());
        formData.append('form_calc_hotels_acc_total', $('#form_calc_hotels_acc_total').text());
        formData.append('form_calc_private_acc_total', $('#form_calc_private_acc_total').text());
        formData.append('form_calc_others_total', $('#form_calc_others_total').text());
        formData.append('form_calc_total_salary_expense_paid', $('#form_calc_total_salary_expense_paid').text());
        
        // AJAX submission
        $.ajax({
            url: nbnu_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#nbnu-spinning-icon-confirmation').hide();
                $('#nbnu-form-submit').prop('disabled', false).text(STRINGS.submit || 'Submit');
                
                if (response.success) {
                    $('#nbnu-confirmation-message').text(response.data.message).removeClass('nbnu-hidden');
                    $('#nbnu-expense-form')[0].reset();
                    $('.nbnu-calculation-display').text('$0.00');
                    $('#form_calc_total_hours_travel_meeting, #form_calc_Less_hours_billed_by_employer, #form_calc_hours_paid').text('0.00');
                    
                    // Scroll to success message
                    $('html, body').animate({
                        scrollTop: $('#nbnu-confirmation-message').offset().top - 100
                    }, 500);
                    
                    // Hide success message after 10 seconds
                    setTimeout(function() {
                        $('#nbnu-confirmation-message').addClass('nbnu-hidden');
                    }, 10000);
                } else {
                    $('#nbnu-global-error-message').text(STRINGS.genericError || 'There was an error submitting your form. Please try again.').removeClass('nbnu-hidden');
                }
            },
            error: function() {
                $('#nbnu-spinning-icon-confirmation').hide();
                $('#nbnu-form-submit').prop('disabled', false).text(STRINGS.submit || 'Submit');
                $('#nbnu-global-error-message').text(STRINGS.genericError || 'There was an error submitting your form. Please try again.').removeClass('nbnu-hidden');
            }
        });
    });

// Initialize calculations on page load
toggleDateSections();
calculateTotalHours();
calculateMileage();
calculateMeals();
calculateAccommodation();

// Hide entire day off and LTD grids on page load
$('[data-row-type="day-off"], [data-row-type="ltd"]').addClass('nbnu-hidden');

// === NEW: Conditional Visibility Logic ===

// Handle employer billing change - show/hide individual day off cells
$(document).on('change', 'input[name*="employer_billing_NBNU"]', function() {
    const name = $(this).attr('name');
    const day = name.match(/form_(.+)_employer_billing_NBNU/)[1];
    const value = $(this).val();
    const dateHasValue = $(`#form_${day}_date`).val().trim() !== '';
    
    if (dateHasValue && value === 'No') {
        // Show only this day's day off options
        $(`.nbnu-day-off-section[data-day="${day}"]`).removeClass('nbnu-hidden');
    } else {
        // Hide this day's day off options and LTD options
        $(`.nbnu-day-off-section[data-day="${day}"]`).addClass('nbnu-hidden');
        $(`.nbnu-ltd-section[data-day="${day}"]`).addClass('nbnu-hidden');
        // Reset values
        $(`input[name="form_${day}_day_off"][value="No"]`).prop('checked', false);
        $(`input[name="form_${day}_LTD_or_WHSCC"][value="Yes"]`).prop('checked', false);
    }
    
    // Check if ANY day has "No" selected to show/hide the row headers
    updateRowVisibility();
    calculateTotalHours();
});

// Handle day off change - show/hide individual LTD cells
$(document).on('change', 'input[name*="day_off"]', function() {
    const name = $(this).attr('name');
    const day = name.match(/form_(.+)_day_off/)[1];
    const value = $(this).val();
    const billingValue = $(`input[name="form_${day}_employer_billing_NBNU"]:checked`).val();
    
    // ✅ Corrected logic:
    // If "Employer billing NBNU" is No and user selects "No" (day off),
    // show the LTD/WHSCC options.
    if (billingValue === 'No' && value === 'No') {
        $(`.nbnu-ltd-section[data-day="${day}"]`).removeClass('nbnu-hidden');
    } else {
        $(`.nbnu-ltd-section[data-day="${day}"]`).addClass('nbnu-hidden');
        $(`input[name="form_${day}_LTD_or_WHSCC"][value="Yes"]`).prop('checked', false);
    }
    
    updateRowVisibility();
    calculateTotalHours();
});


// Function to show/hide row headers based on whether any cells need to be shown
function updateRowVisibility() {
    const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    let showDayOffRow = false;
    let showLTDRow = false;
    
    days.forEach(day => {
        // Check if any day off cell should be visible
        if (!$(`.nbnu-day-off-section[data-day="${day}"]`).hasClass('nbnu-hidden')) {
            showDayOffRow = true;
        }
        
        // Check if any LTD cell should be visible
        if (!$(`.nbnu-ltd-section[data-day="${day}"]`).hasClass('nbnu-hidden')) {
            showLTDRow = true;
        }
    });
    
    // Show/hide the entire grids based on whether any cells need to be shown
    if (showDayOffRow) {
        $('.nbnu-day-off-grid').addClass('show');
    } else {
        $('.nbnu-day-off-grid').removeClass('show');
    }
    
    if (showLTDRow) {
        $('.nbnu-ltd-grid').addClass('show');
    } else {
        $('.nbnu-ltd-grid').removeClass('show');
    }
}

    // On page load, hide all individual cells' content
    $('.nbnu-day-off-section').addClass('nbnu-hidden');
    $('.nbnu-ltd-section').addClass('nbnu-hidden');

    $('#form-files').on('change', function() {
        const files = this.files;
        const $preview = $('#nbnu-uploaded-files-preview');
        $preview.empty();

        if (files.length > 0) {
            $preview.append(`<h4>${STRINGS.selectedFiles || 'Selected Files:'}</h4>`);
            Array.from(files).forEach(file => {
                $preview.append(`<p>📎 ${file.name} (${(file.size / 1024).toFixed(1)} KB)</p>`);
            });
        }
    });
}); // end document ready
