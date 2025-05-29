$(document).ready(function() {
    $('#summarizeBtn').click(function() {

        $('#summary_panel').collapse('toggle');
        
        if ($('#summaryContent').html().indexOf('fa-spinner') !== -1) {
            var paperIds = JSON.parse($(this).attr('data-paper-ids'));
            var keywords = $(this).attr('data-keywords');

            $.ajax({
                url: `${appBaseUrl}/site/summarize`,
                type: 'POST',
                data: {
                    paperIds,
                    keywords,
                    limit: 5,
                },
                success: function(response) {
                    $('#summaryLoading').hide();
                    $('#summaryText').html(response).show();
                },
                error: function() {
                    $('#summaryLoading').hide();
                    $('#summaryText').html('Failed to generate summary').show();
                }
            });
        }
    });
});
