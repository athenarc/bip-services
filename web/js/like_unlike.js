/*
 * Actions for like and unlike links
 *
 * @author: Hlias
 */
$(document).ready(function() {
    var currentWindow = window.location;
    var currentElement = null;
    var parentElement = null;
    var id = null;
    var action = null;

    //Add this behaviour to all heart icons with these classes
    $(document).on('click', '.liked-heart,.not-liked-heart', function() {
        currentElement = $(this);
        parentElement = currentElement.closest('a');
        id = parentElement.attr('id').replace("a_res_", "");

        if(currentElement.hasClass('liked-heart'))
        {
            action = 'ajaxunlike';
            $('#confirm-delete-bookmark').modal('show');
        }
        else
        {
            action = 'ajaxlike';
            likeUnlike(currentWindow, currentElement, id, action);
        }

        return false;
    });

    $('#deletebookmark').on('click', function (e) {
        e.preventDefault();
        $('#confirm-delete-bookmark').modal('hide');
        likeUnlike(currentWindow, currentElement, id, action);
    });

});

function likeUnlike(currentWindow, currentElement, id, action) {

    let detailsReading = function(currentWindow, visibility) {
        // if viewing details
        if(currentWindow.pathname.split('/').slice(-1)[0] == 'details')
        {
            // if no references or citations modal open
            if (!$('#references-modal').hasClass('in') && !$('#citations-modal').hasClass('in'))
            {
                $('#detailsReading').css('visibility', visibility);
            }
        }
    }

    //Required for post requests in yii
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    //Do the required like/unlike action
    $.ajax(
    {
        url:   `${appBaseUrl}/site/${action}`,
        type: 'POST',
        data:
        {
            'paper_id' : id,
            _csrf : csrfToken
        },
        success: function(data)
        {
            //called when successful
            $(currentElement).toggleClass('fa-solid fa-regular liked-heart not-liked-heart');
            if(currentElement.hasClass('not-liked-heart'))
            {
                currentElement.parent('a').attr('title', 'Add to my readings');
                detailsReading(currentWindow, 'hidden');
            }
            else if(currentElement.hasClass('liked-heart'))
            {
                currentElement.parent('a').attr('title', 'Remove from my readings');
                detailsReading(currentWindow, 'visible');
            }

            //if viewing readings page
            if(currentWindow.pathname.split('/').includes('readings'))
            {
                // reload the readings page to update all info
                location.reload();
                
                // // remove the element
                // currentElement.closest('tr').fadeOut(1000, function() {

                //     // update number of articles in folder
                //     updateFavorites(currentElement, csrfToken)
                // });
            }
        },
        error: function(e)
        {
            alert("There was an error processing your request!");
            location.reload();
        }
    });

}

function updateFavorites(currentElement, csrfToken){

    let folderId =  currentElement.closest('table').attr('data-folderid');
    let fa_id = '#fa_'+ folderId;
    if (!isNaN(folderId) && $(fa_id).length > 0){ // folderIs is number and element exists (i.e excluding misc. bookmarks)

        $.ajax(
        {
            url:   `${appBaseUrl}/readings/ajaxupdatefavorites`,
            type: 'POST',
            data:
            {
                'folder_id' : folderId,
                _csrf : csrfToken
            },
            success: function(data)
            {
                // server ajax response
                // data.folder_articles, data.percent_read
                $(fa_id).text(data.folder_articles_str.concat(data.percent_read));
                if (data.folder_articles == "0") {
                    currentElement.closest('table').replaceWith( "<p>No bookmarks in this folder</p>" );

                }
            },
            error: function(e)
            {
                alert("There was an error processing your request!");
                location.reload();
            }
        });

    }

}
