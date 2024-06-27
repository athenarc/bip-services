/*
 * Bookmark tags
 */

$(document).ready(function () {


    $('.bootstrap-tagsinput input').focus(function() {
        $(this).attr('placeholder', 'Add tag...');
    })

    $('.bootstrap-tagsinput input').blur(function() {
        $(this).attr('placeholder', '+');
    })

    $(".tag-options").on('beforeItemAdd beforeItemRemove', function(e) {

        const paper_id =  $(this).closest('.tag-options').attr('name').split("_")[1];
        const tag_name = e.item;
    
        // disable clicks during database update
        $('body').addClass('cursor-wait');
        $('#overwrap').addClass('avoid-clicks');

        //Required for post requests in yii
        var _csrf = $('meta[name="csrf-token"]').attr("content");

        const action_name = (e.type === 'beforeItemAdd') ? 'add-tag' : 'remove-tag';

        $.ajax({
            url: `${window.location.origin}/bip/web/index.php/site/${action_name}`,
            type: 'POST',
            data: {
                tag_name,
                paper_id,
                _csrf
            },
            success: function({ tag_id }) {
                //called when successful
                $('body').removeClass('cursor-wait');
                $('#overwrap').removeClass('avoid-clicks');

                //if viewing readings (but not a reading list)
                let path = window.location.pathname.split('/');
                if(path.pop() == 'list' && path.pop() == 'readings') {
                    let isSelected = (action_name === "add-tag");
                    updateFacet("tag", tag_id, tag_name, isSelected);
                }
            },
            error: function(e) {
                alert("There was an error processing your request!");
                location.reload();
            }
        });
    });
});