function StartPopover() {
    // the function was added also in CustomBootstrapModal,
    // because when a new element is added in the html tree (ajax)
    // popover needs reinitialization !!
    $('[data-toggle="popover"]').popover({
        html: true,
        container: 'body',
    });

    // set a different title when hovering on the element
    $('.impact-icon').each(function () {
        $(this).attr('title', $(this).attr('data-hover-title'));
    });

    // set hover title for reproducibility badges (Bootstrap popover removes native title)
    $('.reproducibility-badge').each(function () {
        const hoverTitle = $(this).attr('data-hover-title');
        if (hoverTitle) {
            $(this).on('mouseenter', function() {
                $(this).attr('title', hoverTitle);
            }).on('mouseleave', function() {
                // Remove title to prevent conflict with popover
                $(this).removeAttr('title');
            });
        }
    });
}

$(() => {
    StartPopover();

    $(document).on('click', e => {
        $('[data-toggle="popover"],[data-original-title]').each(function () {
            // the 'is' for buttons that trigger popups
            // the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                (($(this).popover('hide').data('bs.popover') || {}).inState || {}).click = false; // fix for BS 3.3.6
            }
        });
    });
});
