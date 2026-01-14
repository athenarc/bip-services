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
            url: `${appBaseUrl}/site/get-top-topics${queryString}`,
            type: 'GET',
            success: function (data) {
                $('#top_topics_in_results').html(data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching topics:', error);
            },
        });
    })();

    // Handle topic evolution visualization button click
    $('#visualize-topic-evolution-btn').on('click', function() {
        const queryString = getQueryString();
        if (!queryString) {
            return;
        }

        // Update modal title
        $('#topicModalLabel').text('Topic Evolution (Last 10 Years)');

        // Show loading state
        $('#top-topics-modal .modal-body').html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-2x grey-text"></i><p class="grey-text" style="margin-top: 15px;">Loading visualization data...</p></div>');

        $.ajax({
            url: `${appBaseUrl}/site/get-top-topics-evolution${queryString}`,
            type: 'GET',
            success: function (data) {
                $('#top-topics-modal .modal-body').html(data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching topic evolution:', error);
                $('#top-topics-modal .modal-body').html('<p class="text-danger">Error loading visualization data. Please try again.</p>');
            },
        });
    });

    $(document).on('click', '.topic-item', function () {
        const queryString = getQueryString();
        if (!queryString) {
            return;
        }

        const topicName = $(this).data('topic-name');

        // Set modal title
        $('#topicModalLabel').text(`Topic Statistics for "${topicName}" (Last 20 Years)`);

        // Show loading state
        $('#top-topics-modal .modal-body').html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-2x grey-text"></i><p class="grey-text" style="margin-top: 15px;">Loading visualization data...</p></div>');

        // Perform AJAX request
        $.ajax({
            url: `${appBaseUrl}/site/get-topic-evolution${queryString}`,
            type: 'GET',
            data: { selectedTopTopic: topicName },
            success: function (data) {
                $('#top-topics-modal .modal-body').html(data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching topic details:', error);
                $('#top-topics-modal .modal-body').text('Error loading data.');
            },
        });

        // Show the modal
        $('#top-topics-modal').modal('show');
    });
});

