let readingListSummaryQuotaReached = false;

function updateReadingListSummaryUsage() {
    const summarizeIcon = $('#reading-list-summarize-btn');
    if (!summarizeIcon.length) { return; }

    $.get(appBaseUrl + '/site/check-summary-quota', response => {
        if (response.quotaReached) {
            readingListSummaryQuotaReached = true;
            summarizeIcon
                .addClass('disabled')
                .attr('aria-disabled', 'true')
                .attr('title', 'You have reached your daily summarization limit.');
        }
    });
}

$('#new-reading-list-modal').on('show.bs.modal', function (event) {
    const relatedTarget = /** @type {any} */ (event).relatedTarget;
    const trigger = $(relatedTarget || []);
    const mode = trigger.data('mode') || 'create';

    const modalTitle = $('#reading-list-modal-title');
    const modalSubmit = $('#reading-list-modal-submit');
    const listIdInput = $('#reading_list_id');
    const titleInput = $('#new_reading_list_title');
    const descriptionInput = $('#new_reading_list_description');

    if (mode === 'edit') {
        modalTitle.text('Edit reading list');
        modalSubmit.text('Update');
        listIdInput.val(trigger.data('reading-list-id') || '');
        titleInput.val(trigger.data('reading-list-title') || '');
        descriptionInput.val(trigger.data('reading-list-description') || '');
        return;
    }

    if (mode === 'duplicate') {
        modalTitle.text('Duplicate reading list');
        modalSubmit.text('Save');
        listIdInput.val('');
        titleInput.val('');
        descriptionInput.val(trigger.data('reading-list-description') || '');
        return;
    }

    modalTitle.text('Create new reading list');
    modalSubmit.text('Save');
    listIdInput.val('');
    titleInput.val('');
    descriptionInput.val('');

    readingListSummaryQuotaReached = false;
    $('#reading-list-summarize-btn')
        .removeClass('disabled')
        .attr('aria-disabled', 'false')
        .attr('title', 'Use AI to summarize top results and fill the description automatically.');
    updateReadingListSummaryUsage();
});

$(document).on('click', '#reading-list-summarize-btn', function () {
    if (readingListSummaryQuotaReached) { return; }

    const btn = $(this);
    const descriptionInput = $('#new_reading_list_description');
    const allPaperIds = JSON.parse(String($('#reading-list-summarize-paper-ids').val() || '[]'));
    const threshold = parseInt(String($('#reading-list-summarize-threshold').val() || '20'), 10) || 20;
    const maxAvailable = allPaperIds.length;

    if (!maxAvailable) {
        btn.attr('title', 'No papers available to summarize.');
        return;
    }

    const limit = Math.min(5, maxAvailable);
    const paperIds = allPaperIds.slice(0, limit);

    btn
        .addClass('disabled')
        .attr('aria-disabled', 'true')
        .attr('title', 'Generating summary...')
        .html('<i class="fa fa-spinner fa-spin"></i> Generating...');

    $.post(appBaseUrl + '/site/summarize', { paperIds, keywords: '', limit })
        .done(response => {
            if (response.error) {
                btn.attr('title', response.error);
                if (String(response.error).toLowerCase().includes('quota')) {
                    readingListSummaryQuotaReached = true;
                }
                return;
            }

            descriptionInput.val(response.plain || '');
            btn.attr('title', 'Autogenerate description using AI summarization');
        })
        .fail(() => {
            btn.attr('title', 'Failed to generate summary.');
        })
        .always(() => {
            if (!readingListSummaryQuotaReached) {
                btn.removeClass('disabled').attr('aria-disabled', 'false');
            }
            updateReadingListSummaryUsage();
            if (!readingListSummaryQuotaReached) {
                btn.html('<i class="fa-solid fa-wand-magic-sparkles"></i> Autogenerate with AI');
            }
            if (readingListSummaryQuotaReached) {
                btn.attr('title', 'You have reached the daily limit of ' + threshold + ' uses for this feature.');
                btn.html('<i class="fa-solid fa-wand-magic-sparkles"></i> Autogenerate with AI');
            }
        });
});

$(function () {
    const description = $('#reading-list-description');
    const toggle = $('#reading-list-description-toggle');

    if (!description.length || !toggle.length) {
        return;
    }

    toggle.on('click', function () {
        const expanded = description.attr('data-expanded') === '1';
        if (expanded) {
            description.html(description.attr('data-short-html') || '');
            description.attr('data-expanded', '0');
            toggle.text('See more');
        } else {
            description.html(description.attr('data-full-html') || '');
            description.attr('data-expanded', '1');
            toggle.text('See less');
        }
    });
});

$(function () {
    const sortableLists = $('.js-reading-lists-sortable');
    if (!sortableLists.length) {
        return;
    }
    const isSmallScreen = window.matchMedia('(max-width: 1640px)').matches;

    function initSortable($list) {
        if (!$list.length || $list.data('sortable-initialized')) {
            return;
        }

        if (isSmallScreen && typeof Sortable !== 'undefined') {
            Sortable.create($list.get(0), {
                handle: '.reading-list-drag-handle',
                animation: 150,
                onStart: function () {
                    $list.data('is-dragging', true);
                },
                onEnd: function () {
                    const orderedIds = Array.from($list.get(0).querySelectorAll(':scope > li[data-list-id]'))
                        .map(item => item.getAttribute('data-list-id'));

                    $.post(appBaseUrl + '/readings/ajax-update-reading-lists-order', {
                        ordered_ids: orderedIds
                    });
                    setTimeout(function () {
                        $list.data('is-dragging', false);
                    }, 0);
                }
            });
        } else if ($.fn.sortable) {
            $list.sortable({
                items: '> li[data-list-id]',
                handle: '.reading-list-drag-handle',
                cancel: '.reading-list-item-title, .fa-info-circle',
                axis: 'y',
                tolerance: 'pointer',
                distance: 5,
                helper: 'clone',
                start: function () {
                    $list.data('is-dragging', true);
                },
                stop: function () {
                    setTimeout(function () {
                        $list.data('is-dragging', false);
                    }, 0);
                },
                update: function () {
                    const orderedIds = $(this)
                        .find('> li[data-list-id]')
                        .map(function () { return $(this).data('list-id'); })
                        .get();

                    $.post(appBaseUrl + '/readings/ajax-update-reading-lists-order', {
                        ordered_ids: orderedIds
                    });
                }
            });
        }

        $list.data('sortable-initialized', true);
    }

    // Initialize any currently visible sortable lists (desktop).
    sortableLists.each(function () {
        const $list = $(this);
        if ($list.is(':visible')) {
            initSortable($list);
        }
    });

    // Initialize compact/mobile list when collapse is opened.
    $('#reading-lists-nav-collapse').on('shown.bs.collapse', function () {
        $(this).find('.js-reading-lists-sortable').each(function () {
            const $list = $(this);
            initSortable($list);
            if (!isSmallScreen && $list.data('sortable-initialized') && $.fn.sortable) {
                $list.sortable('refresh');
            }
        });
    });

    // On touch, prevent accidental navigation when the gesture was a drag.
    $(document).on('click', '.js-reading-lists-sortable .toc-link', function (e) {
        const $parentList = $(this).closest('.js-reading-lists-sortable');
        if ($parentList.data('is-dragging')) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
