$(document).ready(() => {
    // Hide the disabled placeholder option in the dropdown when it opens
    // This prevents "-- Select a reason --" from appearing as a selectable option
    $('#report-reason').on('focus mousedown', function () {
        $(this).find('option[disabled]').css('display', 'none');
    });

    // Character counter for description textarea
    $('#report-description').on('input', function () {
        const length = $(this).val().length;
        $('#report-description-count').text(length);
    });

    // Reset form when modal is closed
    $('#reportProfileModal').on('hidden.bs.modal', () => {
        $('#report-profile-form')[0].reset();
        $('#report-description-count').text('0');
        $('#report-message').hide().removeClass('alert-success alert-danger').text('');
        $('#submit-report-btn').prop('disabled', false).text('Submit Report');
    });

    // Handle form submission
    $('#submit-report-btn').click(e => {
        e.preventDefault();

        const reason = $('#report-reason').val();
        const description = $('#report-description').val();
        const reportedOrcid = $('#report-profile-orcid').val();
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Validate form
        if (!reason) {
            showReportMessage('Please select a reason for reporting.', 'danger');
            return;
        }

        // Disable submit button to prevent double submission
        const $submitBtn = $('#submit-report-btn');
        $submitBtn.prop('disabled', true).text('Submitting...');

        // Submit report via AJAX
        $.ajax({
            url: `${appBaseUrl }/scholar/report-profile`,
            type: 'POST',
            data: {
                'reported_orcid': reportedOrcid,
                'reason': reason,
                'description': description,
                _csrf: csrfToken,
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    showReportMessage(response.message, 'success');
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        $('#reportProfileModal').modal('hide');
                    }, 2000);
                } else {
                    showReportMessage(response.message || 'An error occurred while submitting your report.', 'danger');
                    $submitBtn.prop('disabled', false).text('Submit Report');
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = 'An error occurred while submitting your report. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showReportMessage(errorMessage, 'danger');
                $submitBtn.prop('disabled', false).text('Submit Report');
            },
        });
    });

    function showReportMessage(message, type) {
        const $messageDiv = $('#report-message');
        $messageDiv.removeClass('alert-success alert-danger alert-warning alert-info')
            .addClass(`alert-${ type}`)
            .text(message)
            .show();

        // Scroll to message
        $messageDiv[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Character counter for template feedback textarea
    $('#template-feedback-message').on('input', function () {
        const length = $(this).val().length;
        $('#template-feedback-description-count').text(length);
    });

    // Reset template feedback form when modal is closed
    $('#templateFeedbackModal').on('hidden.bs.modal', () => {
        const $form = $('#template-feedback-form');
        if ($form.length) {
            $form[0].reset();
        }
        $('#template-feedback-description-count').text('0');
        $('#template-feedback-message-box').hide().removeClass('alert-success alert-danger').text('');
        $('#submit-template-feedback-btn').prop('disabled', false).text('Submit Feedback');
    });

    // Submit profile feedback to template creator
    $('#submit-template-feedback-btn').click(e => {
        e.preventDefault();

        const message = $('#template-feedback-message').val();
        const profileOrcid = $('#template-feedback-profile-orcid').val();
        const templateId = $('#template-feedback-template-id').val();
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        if (!message || !message.trim()) {
            showTemplateFeedbackMessage('Please enter your feedback.', 'danger');
            return;
        }

        const $submitBtn = $('#submit-template-feedback-btn');
        $submitBtn.prop('disabled', true).text('Submitting...');

        $.ajax({
            url: `${appBaseUrl }/scholar/submit-template-feedback`,
            type: 'POST',
            data: {
                template_id: templateId,
                profile_orcid: profileOrcid,
                message: message.trim(),
                _csrf: csrfToken,
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    showTemplateFeedbackMessage(response.message, 'success');
                    setTimeout(() => {
                        $('#templateFeedbackModal').modal('hide');
                    }, 2000);
                } else {
                    showTemplateFeedbackMessage(response.message || 'An error occurred while submitting feedback.', 'danger');
                    $submitBtn.prop('disabled', false).text('Submit Feedback');
                }
            },
            error: function (xhr) {
                let errorMessage = 'An error occurred while submitting feedback. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showTemplateFeedbackMessage(errorMessage, 'danger');
                $submitBtn.prop('disabled', false).text('Submit Feedback');
            },
        });
    });

    function showTemplateFeedbackMessage(message, type) {
        const $messageDiv = $('#template-feedback-message-box');
        $messageDiv.removeClass('alert-success alert-danger alert-warning alert-info')
            .addClass(`alert-${ type}`)
            .text(message)
            .show();

        $messageDiv[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
