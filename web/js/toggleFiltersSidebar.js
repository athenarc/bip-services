// Restore filters sidebar state immediately to prevent visual delay
(function() {
    const FILTERS_SIDEBAR_STORAGE_KEY = 'bip_filters_sidebar_state';
    const savedState = localStorage.getItem(FILTERS_SIDEBAR_STORAGE_KEY);
    
    if (savedState === 'open') {
        // Try to apply immediately, then retry if DOM not ready
        function applyState() {
            var filters = document.getElementById('search_filters');
            var button = document.getElementById('collapse_filters_button');
            if (filters && button) {
                filters.classList.remove('toggled');
                button.classList.remove('toggled');
            } else {
                // Retry if elements not found yet
                setTimeout(applyState, 10);
            }
        }
        applyState();
    }
})();

$(document).ready(() => {
    const FILTERS_SIDEBAR_STORAGE_KEY = 'bip_filters_sidebar_state';
    
    // Restore saved state on page load (fallback in case early restoration didn't work)
    const savedState = localStorage.getItem(FILTERS_SIDEBAR_STORAGE_KEY);
    if (savedState === 'open') {
        // Remove 'toggled' class to open the sidebar
        $('#search_filters').removeClass('toggled');
        $('#collapse_filters_button').removeClass('toggled');
    } else {
        // Ensure sidebar is closed (toggled class should already be present from HTML)
        $('#search_filters').addClass('toggled');
        $('#collapse_filters_button').addClass('toggled');
    }
    
    // Toggle sidebar and save state
    $('#search_filters_toggle_button').click(() => {
        $('#search_filters').toggleClass('toggled');
        $('#collapse_filters_button').toggleClass('toggled');
        
        // Save state: 'open' if not toggled (sidebar is visible), 'closed' if toggled (sidebar is hidden)
        const newState = $('#search_filters').hasClass('toggled') ? 'closed' : 'open';
        localStorage.setItem(FILTERS_SIDEBAR_STORAGE_KEY, newState);
    });


    // when no results are shown in index page, filters sidebar goes below the footer.
    // #search_filters (sidebar) is absolute positioned, and as such the height of the body doesn't
    // automatically adjust, in order to push down the footer below the sidebar.
    const adjustPageHeight = () => {
        const autocompleteHeight = $('.ui-autocomplete').outerHeight() || 0;
        const sidebarHeight = $('#search_filters').outerHeight();
        const contentHeight = $('#overwrap').height();
        const footerHeight = $('footer').height();
        if (contentHeight - footerHeight < sidebarHeight + autocompleteHeight) {
            $('#overwrap').css('height', document.documentElement.scrollHeight);
        }
    };

    // Adjust page height after restoring state
    adjustPageHeight();

    $('#topics_search_box').on('autocompleteopen', () => {
        adjustPageHeight();
    });
});
