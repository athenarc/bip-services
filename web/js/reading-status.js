/*
 * Bookmark reading status
 */

$(document).ready(function () {

    let updateColor = function(elem) {
        // reading-status-color
        elem.attr('data-color',elem.val())
        //lose focus
        elem.blur();
    };

    var previousReadingValue;
    $(".reading-status").on('focus', function () {
        // Store the current value on focus and on change
        previousReadingValue = this.value;
    }).change (function () {
        var currentElement = $(this);
        var readingValue = currentElement.val();
        var path = window.location.pathname.split('/');

        if(path.includes('favorites') || path.includes('readings'))
        {
            var paperId = currentElement.attr('name').split('_')[1];

        }	else if (path.includes('details')) {

            var paperId = currentElement.closest('div').attr('data-paperid');
        }

        // disable clicks during database update
        $('body').addClass('cursor-wait');
        $('#overwrap').addClass('avoid-clicks');

        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        //Do the required action
        $.ajax(
        {
            url:   window.location.origin + '/bip/web/index.php/readings/ajax-reading',
            type: 'POST',
            data:
            {
                'reading_value' : readingValue,
                'previous_reading_value' : previousReadingValue,
                'paper_id' : paperId,
                _csrf : csrfToken
            },
            success: function({reading_name, previous_reading_name})
            {
                //called when successful
                updateColor(currentElement);
                $('body').removeClass('cursor-wait');
                $('#overwrap').removeClass('avoid-clicks');

                if(path.includes('favorites')) {
                    // update number of articles in folder
                    // function updateFavorites declared in file like_unlike.js
                    updateFavorites(currentElement, csrfToken)

                } else if (path.includes('readings')) {
                    // update-add the new reading-status
                    updateFacet("rd_status", readingValue, reading_name, true);
                    // update-remove the old reading-status
                    updateFacet("rd_status", previousReadingValue, previous_reading_name, false);
                }

            },
            error: function(e)
            {
                alert("There was an error processing your request!");
                location.reload();
            }
        });

    });

});