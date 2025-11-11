/**
 * Form Helper
 * A reusable JavaScript utility for handling form submissions
 *
 * Usage:
 *   Form.submit('#user-form', {
 *       success: function(response) { ... },
 *       error: function(xhr) { ... }
 *   });
 */

class FormHelper {
    /**
     * Submit form via AJAX
     */
    submit(formSelector, options = {}) {
        const form = $(formSelector);
        if (!form.length) {
            console.error(`Form "${formSelector}" not found`);
            return;
        }

        const formData = form.serialize();
        const formAction = form.attr('action');
        const formMethod = form.find('input[name="_method"]').val() || form.attr('method') || 'POST';

        // Clear previous errors
        this.clearErrors(form);

        $.ajax({
            url: formAction,
            method: formMethod === 'PUT' || formMethod === 'PATCH' ? 'POST' : formMethod,
            data: formData + (formMethod === 'PUT' || formMethod === 'PATCH' ? `&_method=${formMethod}` : ''),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
                ...options.headers
            },
            success: function(response) {
                if (options.success) {
                    options.success(response);
                } else {
                    // Default success handler
                    if (window.Toast) {
                        window.Toast.success(response.message || 'Operation completed successfully.');
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    FormHelper.displayValidationErrors(form, errors);

                    if (window.Toast) {
                        window.Toast.error(xhr.responseJSON.message || 'Validation failed.');
                    }
                } else {
                    if (window.Toast) {
                        window.Toast.error('An error occurred. Please try again.');
                    }
                }

                if (options.error) {
                    options.error(xhr);
                }
            }
        });
    }

    /**
     * Display validation errors
     */
    static displayValidationErrors(form, errors) {
        $.each(errors, function(key, value) {
            const input = form.find(`[name="${key}"]`);
            input.addClass('is-invalid');

            // Find or create error message element
            let errorDiv = form.find(`#${key}-error`);
            if (!errorDiv.length) {
                errorDiv = $(`<div class="invalid-feedback" id="${key}-error"></div>`);
                input.after(errorDiv);
            }
            errorDiv.removeClass('d-none').text(value[0]);
        });
    }

    /**
     * Clear form errors
     */
    clearErrors(form) {
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').addClass('d-none').text('');
    }

    /**
     * Reset form
     */
    reset(formSelector) {
        const form = $(formSelector);
        if (form.length) {
            form[0].reset();
            this.clearErrors(form);
        }
    }
}

// Create global instance
window.Form = new FormHelper();

