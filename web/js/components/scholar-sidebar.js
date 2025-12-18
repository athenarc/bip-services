$(document).ready(() => {
    $('#sidebarCollapse').click(e => {
        e.stopPropagation();
        $('.sidebar-fav').toggleClass('sidebar-appear');
        $('#sidebarCollapse').blur();
    });


    $('.folder-content').click(event => {
        if ($(event.target).attr('id') !== 'sidebarCollapse' && $('#sidebarCollapse').css('display') !== 'none') {
            if ($('.sidebar-fav').hasClass('sidebar-appear')) {
                $('.sidebar-fav').removeClass('sidebar-appear');
            }
        }
    });
});
