function submit_scholar_form() {
    // Save scroll position
    sessionStorage.setItem('scrollPos', $(window).scrollTop());

    $("#loading_results").show();

    $("#publications").hide();
    $("#missing-publications-toggle").hide();
    $("#missing-publications").hide();

    $("#scholar-form").submit();
}


$(document).on('click', '.facet-item', function (e) {
    e.preventDefault();

    let listId    = $(this).data('list-id');
    let facet     = $(this).data('facet');
    let elementId = $(this).attr('id');
    let input     = $(`#${elementId}-i`);

    // Map facet → prefix used in DOM IDs
    let prefixMap = {
        topics: "topic",
        roles: "role",
        accesses: "access",
        types: "type"
    };
    let facetIdPrefix = prefixMap[facet] || facet;

    console.log("Click facet:", { listId, facet, elementId });

    // Hide all buttons in this facet group for this list
    $(`#${facetIdPrefix}-facet-items-${listId} .facet-item`)
        .hide()
        .find('input').prop('disabled', true);

    // Just re-enable the clicked one; let the server apply btn-success
    if (input.length) {
        input.prop('disabled', false);
        $(this).show();
        console.log("Activated:", elementId);
    }

    submit_scholar_form();
});

function clearFacet(listId, facetName) {
    // Re-enable all options (reset to full list) by clearing the filter
    let prefixMap = {
        topics: "topic",
        roles: "role",
        accesses: "access",
        types: "type"
    };
    let facetIdPrefix = prefixMap[facetName] || facetName;

    $(`#${facetIdPrefix}-facet-items-${listId} .facet-item`)
        .show()
        .find('input').prop('disabled', true)
        .closest('button')
        .removeClass('btn-success')
        .addClass('btn-default');

    submit_scholar_form();
}

$(document).ready(function() {
    $('#reading-list-public-switch').click(function(event) {
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var is_public = (event.target.checked) ? 1 : 0;
        var current_reading_list_id = $('#current_reading_list_id').val();

        if(is_public) {
            if(!confirm('You are about to make your reading list publicly accessible through BIP! Scholar’s UI. Are you sure?')){
                event.preventDefault();
                return;
            }
        } else {
            if (!confirm('Are you sure you want to make your reading list private?')){
                event.preventDefault();
                return;
            }
        }

        $.ajax({
            url:   `${appBaseUrl}/readings/ajax-update-public-reading-list`,
            type: 'POST',
            data: {
                'is_public' : is_public,
                'reading_list_id': current_reading_list_id,
                _csrf : csrfToken
            },
            error: function() {
                alert("There was an error processing your request!");
            }
        });
    });

    $("#sort-dropdown").on('change', function(){
        let selected_list_id = $('#scholar-form').attr('data-selected_list_id');
        if (selected_list_id){
            let default_action = $('#scholar-form').attr('action');
            $('#scholar-form').attr('action', default_action + "/" + selected_list_id);
            $("#scholar-form").find("input").attr('disabled', 'disabled');
        }
        submit_scholar_form();
    });
});
