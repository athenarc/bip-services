$(document).ready(function() {

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
            
        let queryString = getQueryString();
        if (!queryString) {
            return;
        }

        $.ajax({
            url: `${appBaseUrl}/site/get-top-topics${queryString}`,
            type: 'GET',
            success: function(data) {
                $('#top_topics_in_results').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching topics:', error);
            }
        });
    })();

    $(document).on('click', '.topic-item', function() {

        let queryString = getQueryString();
        if (!queryString) {
            return;
        }

        var topicName = $(this).data('topic-name');
        
        // Set modal title
        $('#topicModalLabel').text(`Topic evolution for "${topicName}"`);
        
        // Show loading text
        $('#top-topics-modal .modal-body').text('Loading...');
        
        // Perform AJAX request
        $.ajax({
            url: `${appBaseUrl}/site/get-topic-evolution${queryString}`,
            type: 'GET',
            data: { selectedTopTopic: topicName },
            success: function(data) {
                $('#top-topics-modal .modal-body').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching topic details:', error);
                $('#top-topics-modal .modal-body').text('Error loading data.');
            }
        });

        // Show the modal
        $('#top-topics-modal').modal('show');
    });
});

