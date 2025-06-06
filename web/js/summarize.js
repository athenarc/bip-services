$(document).ready(function () {
    
    function generateSummary(limit) {
        const summarizeBtn = $('#summarizeBtn');
        const allPaperIds = JSON.parse(summarizeBtn.attr('data-paper-ids'));
        const keywords = summarizeBtn.attr('data-keywords');
        const paperIds = allPaperIds.slice(0, limit);

        $('#summaryText').hide();
        $('#regenerate-summary-box').hide();
        $('#summaryLoading').show();

        $.ajax({
            url: `${appBaseUrl}/site/summarize`,
            type: 'POST',
            data: {
                paperIds,
                keywords,
                limit
            },
            success: function (response) {
                $('#summaryLoading').hide();
                $('#summaryText').html(response).show();
                $('#regenerate-summary-box').show();
            },
            error: function () {
                $('#summaryLoading').hide();
                $('#summaryText').html('Failed to generate summary.').show();
            }
        });
    }

    $('#summarizeBtn').click(function () {
        $('#summary_panel').collapse('toggle');

        if ($('#summaryContent').html().indexOf('fa-spinner') !== -1) {
            generateSummary(5);
        }
    });

    $(document).on('click', '#regenerate-summary-btn', function () {
        const topN = parseInt($('#summary-count').val(), 10);
        if (isNaN(topN) || topN < 0 || topN > 20) return;
        generateSummary(topN);

    });

    $(document).on('keydown', '#summary-count', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); 
            $('#regenerate-summary-btn').click(); 
        }
    });
});
