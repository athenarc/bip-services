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
                    $('#regenerate-summary-box').show();
                },
                error: function() {
                    $('#summaryLoading').hide();
                    $('#summaryText').html('Failed to generate summary').show();
                }
            });
        }

    });
    
    $(document).on('click', '#regenerate-summary-btn', function () {
        const topN = parseInt($('#summary-count').val(), 10);
        if (isNaN(topN) || topN < 0 || topN > 20) return;

        const summarizeBtn = $('#summarizeBtn');
        const allPaperIds = JSON.parse(summarizeBtn.attr('data-paper-ids'));
        const keywords = summarizeBtn.attr('data-keywords');
        const paperIds = allPaperIds.slice(0, topN);

        $('#summaryText').hide();
        $('#regenerate-summary-box').hide();
        $('#summaryLoading').show();

        $.ajax({
            url: `${appBaseUrl}/site/summarize`,
            type: 'POST',
            data: {
                paperIds: paperIds,
                keywords: keywords,
                limit: topN
            },
            success: function(response) {
                $('#summaryLoading').hide();
                $('#summaryText').html(response).show();
                $('#regenerate-summary-box').show();
            },
            error: function() {
                $('#summaryLoading').hide();
                $('#summaryText').html('Failed to regenerate summary.').show();
            }
        });
    });
});
