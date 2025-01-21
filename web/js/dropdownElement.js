$(document).ready(function () {

    $(document).on('change', '.dropdown-options', function () {
        let optionId = $(this).val();
        let templateId = $('#template_id').val();
        let elementId = $(this).data('element-id');

        $.ajax({
            url: appBaseUrl + '/scholar/save-dropdown-instance',
            type: 'POST',
            data: {
                option_id: optionId,
                template_id: templateId,
                element_id: elementId,
                // _csrf: yii.getCsrfToken() // Ensure CSRF token is included
            },
            success: function (response) {
                if (response.status === 'success') {
                    // alert(response.message);
                    // console.log('Data saved successfully:', response);

                } else if (response.status === 'deleted') {
                    // alert(response.message);
                    // console.log('Data deleted successfully:', response);
                } else if (response.status === 'error') {
                    // console.error(response.errors);
                    alert('Failed to update instance.');
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while processing the request.');
                // console.error('Error saving data:', error);
            }
        });
    });


  });
