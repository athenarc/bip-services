$(document).ready(() => {
    const $summarizeBtn = $('#summarizeBtn');
    const $summaryCount = $('#summary-count');
    const $summaryText = $('#summaryText');
    const $summaryPanel = $('#summary_panel');
    const $summaryUsageInfo = $('#summary-usage-info');
    const $summaryLoading = $('#summaryLoading');
    const $copySummaryWrapper = $('#copy-summary-wrapper');
    const $regenerateBox = $('#regenerate-summary-box');
    const $copyBtn = $('#copy-summary-btn');

    $copyBtn.tooltip();

    const allPaperIds = JSON.parse($summarizeBtn.attr('data-paper-ids'));
    const keywords = $summarizeBtn.attr('data-keywords');
    const maxAvailable = allPaperIds.length;
    const defaultLimit = Math.min(5, maxAvailable);
    const summarizeThreshold = $summarizeBtn.data('threshold') || 20;

    let quotaReached = false;

    $summaryCount.attr({
        min: 1,
        max: Math.min(20, maxAvailable),
    }).val(Math.min(6, maxAvailable));

    checkQuotaOnLoad();

    function checkQuotaOnLoad() {
        $.get(`${appBaseUrl}/site/check-summary-quota`, response => {
            if (response.quotaReached) {
                quotaReached = true;
                $summarizeBtn
                    .prop('disabled', true)
                    .addClass('disabled')
                    .off('click')
                    .attr('title', `You have reached the daily limit of ${summarizeThreshold} uses for this feature.`);
                $summaryText.html('<span class="text-danger">You have reached your daily quota for summarizations.</span>').show();
            }

            if (response.used !== undefined && response.limit !== undefined) {
                updateUsageInfo(response.used, response.limit);
            }
        });
    }

    function updateUsageInfo(used, limit) {
        $summaryUsageInfo
            .html(`You have used <b>${used}</b> out of <b>${limit}</b> AI Assistant attempts for today. Limit resets every 24 hours.`)
            .show();
    }

    function generateSummary(limit) {
        const paperIds = allPaperIds.slice(0, Math.min(limit, maxAvailable));

        $copySummaryWrapper.hide();
        $summaryText.hide();
        $regenerateBox.hide();
        $summaryUsageInfo.hide();
        $summaryLoading.show();

        $.post(`${appBaseUrl}/site/summarize`, { paperIds, keywords, limit })
            .done(response => {
                $summaryLoading.hide();

                if (response.error) {
                    $summaryText.html(`<span class="text-danger">${response.error}</span>`).show();
                    if (response.error.includes('quota')) {
                        quotaReached = true;
                        $summarizeBtn.prop('disabled', true).off('click');
                    }
                    return;
                }

                window.originalSummary = response.plain;
                $summaryText.html(response.html).show();
                $regenerateBox.show();
                $copySummaryWrapper.show();

                // Refresh quota display after each summary
                $.get(`${appBaseUrl}/site/check-summary-quota`, quotaResp => {
                    if (quotaResp.used !== undefined && quotaResp.limit !== undefined) {
                        updateUsageInfo(quotaResp.used, quotaResp.limit);
                    }
                });
            })
            .fail(() => {
                $summaryLoading.hide();
                $summaryText.html('Failed to generate summary.').show();
            });
    }

    $summarizeBtn.on('click', () => {
        if (quotaReached) { return; }

        const isCollapsed = !$summaryPanel.hasClass('in') && !$summaryPanel.is(':visible');
        const hasSummary = !!$summaryText.html().trim();

        $summaryPanel.collapse('toggle');

        if (isCollapsed && !hasSummary) {
            generateSummary(defaultLimit);
        }
    });

    $(document).on('click', '#regenerate-summary-btn', () => {
        if (quotaReached) { return; }

        const topN = parseInt($summaryCount.val(), 10);
        if (isNaN(topN) || topN < 0 || topN > 20) { return; }

        generateSummary(topN);
    });

    $(document).on('keydown', '#summary-count', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#regenerate-summary-btn').click();
        }
    });

    $copyBtn.on('click', () => {
        if (!window.originalSummary) { return; }

        navigator.clipboard.writeText(window.originalSummary).then(() => {
            $copyBtn.attr('data-original-title', 'Summary copied!').tooltip('show').off('mouseenter focus');

            setTimeout(() => {
                $copyBtn.tooltip('hide').removeAttr('data-original-title');
            }, 1500);
        }).catch(err => {
            console.error('Failed to copy summary.', err);
        });
    });
});
