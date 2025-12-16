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
});
