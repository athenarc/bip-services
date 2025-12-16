/*
 * Confirmation pop-up before deleting bookmark folder
 */

$(document).ready(() => {
    let formsnapshot = null;

    $('.bookmark-delete-button').click(function () {
        const folder_name = $(this).closest('button').attr('data-folder');
        formsnapshot = $(this).closest('form');
        $('#modaldeleteContent').text(folder_name);
        $('#confirm-delete-folder').modal('show');
    });

    $('#deletefolder').click(() => {
        formsnapshot.submit();
    });
});
