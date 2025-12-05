$(document).ready(function () {
    /**
     * Initialize a single summary instance (Finder/Readings or Contributions List).
     * Accepts a button element and optional listId (for per-list instances).
     */
    function initSummaryInstance($button, listId) {
        const isPerList = !!listId;

        // Resolve selectors depending on context (legacy single vs multi-list)
        const $summaryPanel = isPerList
            ? $(`#summary_panel_${listId}`)
            : $('#summary_panel');
        const $summaryCount = isPerList
            ? $(`#summary-count-${listId}`)
            : $('#summary-count');
        const $summaryText = isPerList
            ? $(`#summaryText-${listId}`)
            : $('#summaryText');
        const $summaryUsageInfo = isPerList
            ? $(`#summary-usage-info-${listId}`)
            : $('#summary-usage-info');
        const $summaryLoading = isPerList
            ? $(`#summaryLoading-${listId}`)
            : $('#summaryLoading');
        const $copySummaryWrapper = isPerList
            ? $(`#copy-summary-wrapper-${listId}`)
            : $('#copy-summary-wrapper');
        const $regenerateBox = isPerList
            ? $(`#regenerate-summary-box-${listId}`)
            : $('#regenerate-summary-box');
        const $copyBtn = isPerList
            ? $summaryPanel.find('.copy-summary-btn')
            : $('#copy-summary-btn');

        if (!$button.length || !$summaryPanel.length) {
            return;
        }

    $copyBtn.tooltip();

        const allPaperIds = JSON.parse($button.attr('data-paper-ids') || '[]');
        const keywords = $button.attr('data-keywords') || '';
    const maxAvailable = allPaperIds.length;
        const defaultLimit = Math.min(5, maxAvailable || 0);
        const summarizeThreshold = $button.data('threshold') || 20;

    let quotaReached = false;

        if (maxAvailable > 0) {
            $summaryCount
                .attr({
        min: 1,
        max: Math.min(20, maxAvailable)
                })
                .val(Math.min(6, maxAvailable));
        }

    checkQuotaOnLoad();

    function checkQuotaOnLoad() {
        $.get(`${appBaseUrl}/site/check-summary-quota`, function (response) {
            if (response.quotaReached) {
                quotaReached = true;
                    $button
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
            if (!allPaperIds.length) {
                return;
            }

        const paperIds = allPaperIds.slice(0, Math.min(limit, maxAvailable));

        $copySummaryWrapper.hide();
        $summaryText.hide();
        $regenerateBox.hide();
        $summaryUsageInfo.hide();
        $summaryLoading.show();

        $.post(`${appBaseUrl}/site/summarize`, { paperIds, keywords, limit })
            .done(function (response) {
                $summaryLoading.hide();

                if (response.error) {
                    $summaryText.html(`<span class="text-danger">${response.error}</span>`).show();
                    if (response.error.includes('quota')) {
                        quotaReached = true;
                            $button.prop('disabled', true).off('click');
                    }
                    return;
                }

                    // Store per-instance summary so copy uses the correct text
                    if (!window.originalSummaries) {
                        window.originalSummaries = {};
                    }
                    const key = listId || 'global';
                    window.originalSummaries[key] = response.plain;

                $summaryText.html(response.html).show();
                $regenerateBox.show();
                $copySummaryWrapper.show();

                // Refresh quota display after each summary
                $.get(`${appBaseUrl}/site/check-summary-quota`, function (quotaResp) {
                    if (quotaResp.used !== undefined && quotaResp.limit !== undefined) {
                        updateUsageInfo(quotaResp.used, quotaResp.limit);
                    }
                });
            })
            .fail(function () {
                $summaryLoading.hide();
                $summaryText.html('Failed to generate summary.').show();
            });
    }

        $button.on('click', function () {
        if (quotaReached) return;

        const isCollapsed = !$summaryPanel.hasClass('in') && !$summaryPanel.is(':visible');
        const hasSummary = !!$summaryText.html().trim();

        $summaryPanel.collapse('toggle');

        if (isCollapsed && !hasSummary) {
                generateSummary(defaultLimit || 5);
        }
    });

        // Regenerate button – global id on Finder/Readings, class+data-list-id for lists
        const regenerateSelector = isPerList
            ? `.regenerate-summary-btn[data-list-id="${listId}"]`
            : '#regenerate-summary-btn';

        $(document).on('click', regenerateSelector, function () {
        if (quotaReached) return;

        const topN = parseInt($summaryCount.val(), 10);
        if (isNaN(topN) || topN < 0 || topN > 20) return;

        generateSummary(topN);
    });

        // Enter on input – per list or global
        const countSelector = isPerList
            ? `#summary-count-${listId}`
            : '#summary-count';

        $(document).on('keydown', countSelector, function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
                if (isPerList) {
                    $summaryPanel.find(regenerateSelector).click();
                } else {
            $('#regenerate-summary-btn').click();
                }
        }
    });

        // Copy summary handler
    $copyBtn.on('click', function () {
            const key = listId || 'global';
            if (!window.originalSummaries || !window.originalSummaries[key]) return;

            navigator.clipboard.writeText(window.originalSummaries[key]).then(() => {
            $copyBtn.attr('data-original-title', 'Summary copied!').tooltip('show').off('mouseenter focus');

            setTimeout(() => {
                $copyBtn.tooltip('hide').removeAttr('data-original-title');
            }, 1500);
        }).catch(err => {
            console.error('Failed to copy summary.', err);
        });
    });
    }

    // New multi-instance usage for Contributions Lists
    const $multiButtons = $('.summarizeBtn');
    if ($multiButtons.length) {
        $multiButtons.each(function () {
            const $btn = $(this);
            const listId = $btn.data('listId');
            initSummaryInstance($btn, listId);
        });
    }

    // Backwards-compatible single-instance usage (Finder / Readings)
    const $legacyButton = $('#summarizeBtn');
    if ($legacyButton.length) {
        initSummaryInstance($legacyButton, null);
    }
});