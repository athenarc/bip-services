$(document).ready( () => {

    $('#search_filters_toggle_button').click(function () {
        $('#search_filters').toggleClass('toggled');
        $('#collapse_filters_button').toggleClass('toggled');
    });


    // when no results are shown in index page, filters sidebar goes below the footer.
    // #search_filters (sidebar) is absolute positioned, and as such the height of the body doesn't
    // automatically adjust, in order to push down the footer below the sidebar.
    const adjustPageHeight = () => {
        const autocompleteHeight = $(".ui-autocomplete").outerHeight() || 0;
        const sidebarHeight = $("#search_filters").outerHeight();
        const contentHeight = $("#overwrap").height();
        const footerHeight = $("footer").height();
        if (contentHeight - footerHeight < sidebarHeight + autocompleteHeight) {
            $("#overwrap").css('height', document.documentElement.scrollHeight)
        }
    }

    adjustPageHeight();


    $("#topics_search_box").on("autocompleteopen", function() {
        adjustPageHeight();
      });

});