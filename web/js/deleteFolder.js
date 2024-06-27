/*
 * Confirmation pop-up before deleting bookmark folder
 */

$(document).ready(function () {

    var formsnapshot= null;

    $(".bookmark-delete-button").click(function () {

        var folder_name = $(this).closest("button").attr("data-folder");
        formsnapshot = $(this).closest("form");
        $('#modaldeleteContent').text(folder_name);
        $('#confirm-delete-folder').modal('show');

    });

    $('#deletefolder').click(function () {
        formsnapshot.submit();
    });
});