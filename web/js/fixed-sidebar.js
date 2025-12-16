$(document).ready(() => {
    $('.toc-link').click(function () {
        $(this).find('.caret').toggleClass('collapsed');
    });
});
