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

    // Flag to track if we should reset dropdown after annotations load (set on new search)
    let shouldResetDropdown = false;

    function loadAnnotations(annotationTypeId = 'all') {
        const queryString = getQueryString();
        if (!queryString) {
            return;
        }

        // If shouldResetDropdown flag is set, force 'all' and reset dropdown
        if (shouldResetDropdown) {
            annotationTypeId = 'all';
            const $dropdown = $('#annotation_type_filter');
            if ($dropdown.length) {
                $dropdown.val('all');
            }
            shouldResetDropdown = false; // Reset the flag
        }

        const url = `${appBaseUrl}/site/get-top-annotations${queryString}`;
        const data = annotationTypeId !== 'all' ? { annotation_type_id: annotationTypeId } : {};

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function (data) {
                // The response is the partial view with just the annotations pills
                $('#top_annotations_in_results').html(data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching annotations:', error);
            },
        });
    }

    // Initial load - ensure dropdown is set to 'all'
    (function () {
        // Always set dropdown to 'all' on initial load
        const $dropdown = $('#annotation_type_filter');
        if ($dropdown.length) {
            $dropdown.val('all');
        }
        loadAnnotations('all');
    })();

    // Listen for search form submission to set reset flag
    // This ensures dropdown resets to 'all' after each new search
    $(document).on('submit', '#search-form', function() {
        shouldResetDropdown = true;
    });

    // Handle dropdown change (user explicitly selects a type)
    $(document).on('change', '#annotation_type_filter', function () {
        const selectedType = $(this).val() || 'all';
        shouldResetDropdown = false; // User explicitly changed it, don't reset on next load
        loadAnnotations(selectedType);
    });

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
            url: `${appBaseUrl}/site/get-top-annotation-evolution${queryString}`,
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

