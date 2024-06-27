/*
 * Initialise DIALOG div
 */
$(document).ready(function()
{
    $('#dialog').dialog(
    {
        autoOpen: false,
        open: function()
        {
            var closeBtn = $('.ui-dialog-titlebar-close');
            closeBtn.append('<span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>');
            $('#dialog').append('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
        },
        close: function()
        {
            var closeBtn = $('.ui-dialog-titlebar-close');
            closeBtn.children('span.ui-button-icon-primary').remove();
            $('#dialog').empty();
            $('#dialog').css({'text-align': 'center'});
        },
        //dialogClass: 'success-dialog',
    }).prev(".ui-dialog-titlebar").css({"background": '#5cb85c', 'color': 'white', 'font-size': 'small'});
});