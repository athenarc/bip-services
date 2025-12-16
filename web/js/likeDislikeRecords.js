/*
 * Like/Dislike Records Feature
 * Handles voting on papers/records in search results
 */

$(document).ready(() => {
    /**
     * Apply vote state to buttons
     * @param {jQuery} container - The like-dislike-buttons container
     * @param {string} action - 'like' or 'dislike' or null
     */
    function applyVoteState(container, action) {
        const $likeBtn = container.find('.btn-like');
        const $dislikeBtn = container.find('.btn-dislike');

        // Reset both buttons to inactive state (only colors, icons stay the same)
        $likeBtn.removeClass('btn-danger active-like').addClass('btn-default grey-link');
        $likeBtn.css({
            'background-color': '',
            'color': '',
        });

        $dislikeBtn.removeClass('btn-danger active-dislike').addClass('btn-default grey-link');
        $dislikeBtn.css({
            'background-color': '',
            'color': '',
        });

        // Apply active state if action is set
        if (action === 'like') {
            $likeBtn.removeClass('btn-default grey-link').addClass('active-like');
            $likeBtn.css({
                'background-color': 'var(--main-color)',
                'color': 'white',
            });
        } else if (action === 'dislike') {
            $dislikeBtn.removeClass('btn-default grey-link').addClass('active-dislike');
            $dislikeBtn.css({
                'background-color': 'var(--main-color)',
                'color': 'white',
            });
        }
    }

    /**
     * Reset button states to default (inactive)
     * @param {jQuery} container - The like-dislike-buttons container
     */
    function resetButtonStates(container) {
        applyVoteState(container, null);
    }

    /**
     * Update button states after vote
     * @param {jQuery} container - The like-dislike-buttons container
     * @param {string} voteType - 'like' or 'dislike'
     * @param {Object} response - Server response
     */
    function updateButtonStates(container, voteType, response) {
        if (response.success) {
            if (response.message.includes('removed')) {
                resetButtonStates(container);
            } else {
                applyVoteState(container, voteType);
            }
        }
    }

    /**
     * Handle vote button click
     * @param {jQuery} buttonElement - The clicked button
     * @param {string} voteType - 'like' or 'dislike'
     */
    function handleVote(buttonElement, voteType) {
        const container = buttonElement.closest('.like-dislike-buttons');
        const paperId = container.data('paper-id');
        const paperRank = container.data('paper-rank');

        if (!paperId) {
            console.error('Paper ID not found');
            return;
        }

        // Check if this button is already active (based on custom active classes)
        let isActive = false;
        if (voteType === 'like') {
            isActive = buttonElement.hasClass('active-like');
        } else if (voteType === 'dislike') {
            isActive = buttonElement.hasClass('active-dislike');
        }

        // If active, remove vote; otherwise, save/update vote
        const remove = isActive ? 1 : 0;

        // Get space_url_suffix from hidden input
        const spaceUrlSuffix = $('#space_url_suffix').val() || '';

        // Get query from URL parameter 'keywords'
        const urlParams = new URLSearchParams(window.location.search);
        const query = urlParams.get('keywords') || '';

        // Get ordering from form field or URL param, default to 'popularity'
        let ordering = $('#ordering').val();
        if (!ordering) {
            ordering = urlParams.get('ordering') || 'popularity';
        }

        // Get CSRF token
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Disable button during request
        buttonElement.prop('disabled', true);

        // Send AJAX request
        $.ajax({
            url: `${appBaseUrl}/site/vote-paper`,
            type: 'POST',
            data: {
                paper_id: paperId,
                vote_type: voteType,
                space_url_suffix: spaceUrlSuffix,
                query: query,
                ordering: ordering,
                paper_rank: paperRank || null,
                remove: remove,
                _csrf: csrfToken,
            },
            success: function (response) {
                if (response.success) {
                    updateButtonStates(container, voteType, response);
                } else {
                    alert(response.message || 'Error processing vote');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error processing vote:', error);
                alert('An error occurred while processing your vote.');
            },
            complete: function () {
                // Re-enable button
                buttonElement.prop('disabled', false);
            },
        });
    }

    // Handle like button clicks
    $(document).on('click', '.btn-like', function (e) {
        e.preventDefault();
        handleVote($(this), 'like');
    });

    // Handle dislike button clicks
    $(document).on('click', '.btn-dislike', function (e) {
        e.preventDefault();
        handleVote($(this), 'dislike');
    });
});

