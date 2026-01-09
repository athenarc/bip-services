/*
 * Confirm/Report Annotations Feature
 * Handles user feedback on annotations in popovers
 */

$(document).ready(() => {
    /**
     * Apply vote state to buttons
     * @param {jQuery} container - The confirm/report annotation buttons container
     * @param {string} action - 'like' or 'dislike' or null (stored values)
     */
    function applyVoteState(container, action) {
        const $likeBtn = container.find('.btn-like-annotation');
        const $dislikeBtn = container.find('.btn-dislike-annotation');

        // Reset both buttons to inactive state (only colors/classes, icons stay the same)
        $likeBtn.removeClass('btn-primary active-like-annotation').addClass('btn-default grey-link');
        $likeBtn.css({
            'background-color': '',
            'color': '',
        });

        $dislikeBtn.removeClass('btn-danger active-dislike-annotation').addClass('btn-default grey-link');
        $dislikeBtn.css({
            'background-color': '',
            'color': '',
        });

        // Apply active state if action is set
        if (action === 'like') {
            $likeBtn.removeClass('btn-default grey-link').addClass('active-like-annotation');
            $likeBtn.css({
                'background-color': 'var(--main-color)',
                'color': 'white',
            });
        } else if (action === 'dislike') {
            $dislikeBtn.removeClass('btn-default grey-link').addClass('active-dislike-annotation');
            $dislikeBtn.css({
                'background-color': 'var(--main-color)',
                'color': 'white',
            });
        }
    }

    /**
     * Reset button states to default (inactive)
     * @param {jQuery} container - The confirm/report annotation buttons container
     */
    function resetButtonStates(container) {
        applyVoteState(container, null);
    }

    /**
     * Update button states after vote
     * @param {jQuery} container - The confirm/report annotation buttons container
     * @param {string} voteType - 'like' or 'dislike' (stored values)
     * @param {Object} response - Server response
     */
    function updateButtonStates(container, voteType, response) {
        if (response.success) {
            if (response.message.includes('removed') || !response.user_vote) {
                resetButtonStates(container);
            } else {
                applyVoteState(container, response.user_vote);
            }
        }
    }

    /**
     * Handle vote button click
     * @param {jQuery} buttonElement - The clicked button
     * @param {string} voteType - 'like' or 'dislike'
     */
    function handleVote(buttonElement, voteType) {
        const container = buttonElement.closest('.like-dislike-annotation-buttons');
        const paperId = container.data('paper-id');
        const annotationTypeId = container.data('annotation-type-id');
        const annotationId = container.data('annotation-id');
        const annotationName = container.data('annotation-name');
        const spaceUrlSuffix = container.data('space-url-suffix');

        if (!paperId || !annotationTypeId || !annotationId || !annotationName || !spaceUrlSuffix) {
            console.error('Missing required data attributes');
            return;
        }

        // Check if this button is already active (based on custom active classes)
        let isActive = false;
        if (voteType === 'like') {
            isActive = buttonElement.hasClass('active-like-annotation');
        } else if (voteType === 'dislike') {
            isActive = buttonElement.hasClass('active-dislike-annotation');
        }

        // If active, remove vote; otherwise, save/update vote
        const remove = isActive ? 1 : 0;

        // Get CSRF token
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Disable button during request
        buttonElement.prop('disabled', true);

        // Send AJAX request
        $.ajax({
            url: `${appBaseUrl}/site/vote-annotation`,
            type: 'POST',
            data: {
                paper_id: paperId,
                annotation_type_id: annotationTypeId,
                annotation_id: annotationId,
                annotation_name: annotationName,
                space_url_suffix: spaceUrlSuffix,
                vote_type: voteType,
                remove: remove,
                _csrf: csrfToken,
            },
            success: function (response) {
                if (response.success) {
                    updateButtonStates(container, voteType, response);
                } else {
                    // Don't show alert if voting is not enabled or if it's a silent fail
                    if (response.silent_fail || (response.message && response.message.includes('not enabled'))) {
                        // Silently fail - buttons should be hidden anyway
                        return;
                    }
                    console.warn('Vote error:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error processing vote:', error);
                // Don't show alert - fail silently
            },
            complete: function () {
                // Re-enable button
                buttonElement.prop('disabled', false);
            },
        });
    }


    // Handle like button clicks (using event delegation for dynamically loaded content)
    $(document).on('click', '.btn-like-annotation', function (e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent popover from closing
        handleVote($(this), 'like');
    });

    // Handle dislike button clicks
    $(document).on('click', '.btn-dislike-annotation', function (e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent popover from closing
        handleVote($(this), 'dislike');
    });

    // Clean up state when popover is hidden so we don't see old selection flashing next time
    $(document).on('hidden.bs.popover', e => {
        const $trigger = $(e.target);
        const popoverId = $trigger.attr('aria-describedby');
        if (!popoverId) { return; }

        const $popover = $(`#${ popoverId}`);
        if ($popover.length === 0) { return; }

        const $container = $popover.find('.like-dislike-annotation-buttons');
        if ($container.length === 0) { return; }

        resetButtonStates($container);
    });

    // Load vote states when popover is shown (to reflect latest DB state)
    $(document).on('shown.bs.popover', e => {
        const $trigger = $(e.target);
        const popoverId = $trigger.attr('aria-describedby');

        if (!popoverId) { return; }

        const $popover = $(`#${ popoverId}`);
        if ($popover.length === 0) { return; }

        const $container = $popover.find('.like-dislike-annotation-buttons');
        if ($container.length === 0) { return; }

        // Immediately reset to empty state to prevent blinking
        resetButtonStates($container);

        // Load the correct state for this annotation
        const paperId = $container.data('paper-id');
        const spaceUrlSuffix = $container.data('space-url-suffix');
        const annotationTypeId = $container.data('annotation-type-id');
        const annotationId = $container.data('annotation-id');

        if (!paperId || !spaceUrlSuffix || !annotationTypeId || !annotationId) { return; }

        // Get CSRF token
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Load vote state
        $.ajax({
            url: `${appBaseUrl}/site/get-user-annotation-votes`,
            type: 'POST',
            data: {
                paper_id: paperId,
                space_url_suffix: spaceUrlSuffix,
                _csrf: csrfToken,
            },
            success: function (response) {
                if (response.success && response.votes) {
                    const action = response.votes[annotationId] || null;
                    applyVoteState($container, action);
                }
            },
            error: function () {
                // Fail silently - buttons stay in server-rendered state
            },
        });
    });
});

