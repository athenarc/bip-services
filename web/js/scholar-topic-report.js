$(document).ready(() => {
    const baseUrl = (typeof window.appBaseUrl === 'string') ? window.appBaseUrl : '';
    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function strikeThroughTopic(paperId, topicId) {
        const selector = `.scholar-topic-tag[data-paper-id="${ paperId }"][data-topic-id="${ topicId }"]`;
        const $topicTag = $(selector);
        if ($topicTag.length === 0) {
            return;
        }

        $topicTag.addClass('topic-reported');
        $topicTag.find('.scholar-topic-label').css({
            'text-decoration': 'line-through',
            'opacity': '0.6',
        });
    }

    function unstrikeTopic(paperId, topicId) {
        const selector = `.scholar-topic-tag[data-paper-id="${ paperId }"][data-topic-id="${ topicId }"]`;
        const $topicTag = $(selector);
        if ($topicTag.length === 0) {
            return;
        }
        $topicTag.removeClass('topic-reported');
        $topicTag.find('.scholar-topic-label').css({
            'text-decoration': '',
            'opacity': '',
        });
    }

    function decrementTopicFacetCount(listId, topicId) {
        const state = { hadBadge: false };
        if (!listId) {
            return state;
        }

        const buttonId = `topic-${ topicId }-list${ listId }`;
        const inputId = `${ buttonId }-i`;
        const $container = $(`#topic-facet-items-${ listId }`);
        const $button = $container.find('button.facet-item').filter((_, el) => el.id === buttonId);
        const $input = $container.find('input[type="hidden"]').filter((_, el) => el.id === inputId);

        if ($button.length) {
            const $badge = $button.find('.badge');
            state.hadBadge = $badge.length > 0;
            const currentCount = $badge.length ? parseInt($badge.text(), 10) : NaN;

            if (Number.isInteger(currentCount) && currentCount > 1) {
                $badge.text(currentCount - 1);
            } else {
                $button.remove();
                if ($input.length) {
                    $input.remove();
                }
            }
        }

        if ($container.length && $container.find('button.facet-item').length === 0) {
            $container.text('-');
        }

        return state;
    }

    function incrementTopicFacetCount(listId, topicId, topicName, withBadge) {
        if (!listId) {
            return;
        }

        const buttonId = `topic-${ topicId }-list${ listId }`;
        const inputId = `${ buttonId }-i`;
        const $container = $(`#topic-facet-items-${ listId }`);
        let $button = $container.find('button.facet-item').filter((_, el) => el.id === buttonId);

        if ($button.length) {
            const $badge = $button.find('.badge');
            if ($badge.length) {
                const currentCount = parseInt($badge.text(), 10);
                const nextCount = Number.isInteger(currentCount) && currentCount > 0 ? currentCount + 1 : 1;
                $badge.text(nextCount);
            }
            return;
        }

        if ($container.text().trim() === '-') {
            $container.empty();
        }

        const badgeHtml = withBadge ? " <span class='badge badge-primary'>1</span>" : '';
        const safeName = escapeHtml(topicName || topicId);
        const buttonHtml = `<button id='${buttonId}'
                type='button'
                class='btn btn-xs btn-default facet-item'
                data-list-id='${listId}'
                data-facet='topics'>
            <input id='${inputId}'
                name='lists[${listId}][topics][]'
                value='${escapeHtml(topicId)}'
                form='scholar-form'
                type='hidden'
                disabled='disabled'/>
            ${safeName}${badgeHtml}
        </button>`;
        $container.append(buttonHtml);
    }

    $(document).on('click', '.report-topic-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const paperId = parseInt($btn.data('paper-id'), 10);
        const topicId = String($btn.data('topic-id') || '').trim();
        const listId = $btn.data('list-id');
        const ownerUserId = parseInt($btn.data('owner-user-id'), 10) || 0;
        const topicName = String($btn.data('topic-name') || '').trim();
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        if (!Number.isInteger(paperId) || paperId <= 0 || topicId === '') {
            alert('Unable to report topic: invalid topic or paper id.');
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: `${baseUrl}/scholar/report-scholar-topic`,
            type: 'POST',
            dataType: 'json',
            data: {
                paper_id: paperId,
                topic_id: topicId,
                owner_user_id: ownerUserId,
                _csrf: csrfToken,
            },
            success: function (response) {
                if (response && response.success) {
                    if (response.action === 'undone') {
                        unstrikeTopic(paperId, topicId);
                        const hadBadgeState = $btn.data('facet-had-badge');
                        const withBadge = (typeof hadBadgeState === 'undefined') ? true : !!hadBadgeState;
                        incrementTopicFacetCount(listId, topicId, topicName, withBadge);
                        $btn.html('<i class="fa fa-flag"></i> Report');
                        $btn.attr('title', 'Report this topic as irrelevant for this research product.');
                        $btn.attr('data-reported', '0');
                    } else {
                        strikeThroughTopic(paperId, topicId);
                        const facetState = decrementTopicFacetCount(listId, topicId);
                        $btn.data('facet-had-badge', facetState.hadBadge);
                        $btn.html('<i class="fa fa-flag"></i> Undo report');
                        $btn.attr('title', 'Undo this topic report for this research product.');
                        $btn.attr('data-reported', '1');
                    }
                    $btn.prop('disabled', false);
                } else {
                    $btn.prop('disabled', false);
                    alert((response && response.message) ? response.message : 'Unable to report topic.');
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                alert('Unable to report topic.');
            },
        });
    });

    $(document).on('shown.bs.popover', '.scholar-topic-label', function () {
        const $trigger = $(this);
        const describedBy = $trigger.attr('aria-describedby');
        if (!describedBy) {
            return;
        }

        const $popover = $(`#${describedBy}`);
        if (!$popover.length) {
            return;
        }

        const $btn = $popover.find('.report-topic-btn');
        if (!$btn.length) {
            return;
        }

        const $topicTag = $trigger.closest('.scholar-topic-tag');
        const isReported = $topicTag.hasClass('topic-reported');

        if (isReported) {
            $btn.html('<i class="fa fa-flag"></i> Undo report');
            $btn.attr('title', 'Undo this topic report for this research product.');
            $btn.attr('data-reported', '1');
        } else {
            $btn.html('<i class="fa fa-flag"></i> Report');
            $btn.attr('title', 'Report this topic as irrelevant for this research product.');
            $btn.attr('data-reported', '0');
        }
    });
});
