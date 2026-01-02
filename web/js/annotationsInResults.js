$(document).ready(() => {
    function getQueryString() {
        let queryString = window.location.search;

        // if no query string, do nothing
        if (!queryString) {
            return null;
        }

        // get space name from hidden input field
        const space = $('#space_url_suffix').val();

        // add space name to query string if it exists
        queryString += (space) ? `&space_url_suffix=${space}` : '';

        return queryString;
    }

    (function () {
        const queryString = getQueryString();
        if (!queryString) {
            return;
        }

        $.ajax({
            url: `${appBaseUrl}/site/get-top-annotations${queryString}`,
            type: 'GET',
            success: function (data) {
                $('#top_annotations_in_results').html(data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching annotations:', error);
            },
        });
    })();

    $(document).on('click', '.annotation-item', function () {
        const queryString = getQueryString();
        if (!queryString) {
            return;
        }

        const annotationName = $(this).data('annotation-name');
        const annotationNameCapitalized = annotationName.charAt(0).toUpperCase() + annotationName.slice(1);

        // Set modal title
        $('#annotationModalLabel').text(`Annotation evolution for "${annotationNameCapitalized}"`);

        // Show loading text
        $('#top-annotations-modal .modal-body').text('Loading...');

        // Perform AJAX request
        $.ajax({
            url: `${appBaseUrl}/site/get-annotation-evolution${queryString}`,
            type: 'GET',
            data: { selectedTopAnnotation: annotationName },
            success: function (data) {
                $('#top-annotations-modal .modal-body').html(data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching annotation details:', error);
                $('#top-annotations-modal .modal-body').text('Error loading data.');
            },
        });

        // Show the modal
        $('#top-annotations-modal').modal('show');
    });
});

