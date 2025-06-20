$(document).ready(function () {
    $('#copy-summary-btn').tooltip();
    const summarizeBtn = $('#summarizeBtn');
    const allPaperIds = JSON.parse(summarizeBtn.attr('data-paper-ids'));
    const keywords = summarizeBtn.attr('data-keywords');
    const maxAvailable = allPaperIds.length;
    const defaultLimit = Math.min(6, allPaperIds.length);
    
    
    $('#summary-count').attr({
        min: 1,
        max: Math.min(20, maxAvailable)
    });

    $('#summary-count').val(defaultLimit);

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
                    .off('click')
                    .attr('title', 'You have reached the daily limit of 20 uses for this feature.');
                $('#summaryText').html('<span class="text-danger">You have reached your daily quota for summarizations.</span>').show();        
            }
        }
    });
    
    function generateSummary(limit) {
        const paperIds = allPaperIds.slice(0, Math.min(limit, allPaperIds.length));
        $('#copy-summary-wrapper').hide();
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

                window.originalSummary = response.plain;

                $('#summaryText').html(response.html).show();
                $('#regenerate-summary-box').show();
                $('#copy-summary-wrapper').show();
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
            generateSummary(defaultLimit);
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
    $('#copy-summary-btn').click(function () {
        if (window.originalSummary) {
            const $btn = $('#copy-summary-btn');

            navigator.clipboard.writeText(window.originalSummary).then(() => {
                
                const $btn = $('#copy-summary-btn');
                $btn.attr('data-original-title', 'Summary copied!').tooltip('show');
                $btn.off('mouseenter focus');

                setTimeout(() => {
                    $btn.tooltip('hide');
                    $btn.removeAttr('data-original-title');
                }, 1500);
            }).catch(err => {
                console.error('Failed to copy summary.', err);
            });
        }
    });
});
