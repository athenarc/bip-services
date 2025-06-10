$(document).ready(function () {
    const summarizeBtn = $('#summarizeBtn');
    let quotaReached = false;

    // Check quota on page load
    $.ajax({
        url: `${appBaseUrl}/site/check-summary-quota`,
        type: 'GET',
        success: function (response) {
            if (response.quotaReached) {
                quotaReached = true;
                summarizeBtn
                    .prop('disabled', true)
                    .addClass('disabled')
                    .off('click');
                $('#summaryText').html('<span class="text-danger">You have reached your daily quota for summarizations.</span>').show();        
            }
        }
    });
    
    function generateSummary(limit) {
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
                if (response.error) {
                    $('#summaryText').html(`<span class="text-danger">${response.error}</span>`).show();

                    // If it's a quota error disable the button
                    if (response.error.includes('quota')) {
                        quotaReached = true;
                        $('#summarizeBtn').prop('disabled', true).off('click');
                    }

                    return;
                }

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
        if (quotaReached) return; 

        $('#summary_panel').collapse('toggle');

        if ($('#summaryContent').html().indexOf('fa-spinner') !== -1) {
            generateSummary(5);
        }
    });
    

    $(document).on('click', '#regenerate-summary-btn', function () {
        if (quotaReached) return; 

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
