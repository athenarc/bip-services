function submit_scholar_form() {
    $("#loading_results").show();

    $("#publications").hide();
    $("#missing-publications-toggle").hide();
    $("#missing-publications").hide();

    $("#scholar-form").submit();
}

function updateFacet(facet_type, id, name, selected) {
    let roleElem = $(`#${facet_type}-facet-items > #${facet_type}-${id}`);
    if (roleElem.length > 0) {
        let countElem = roleElem.children('span')
        let count = parseInt(countElem.html());
        count = (selected) ? count + 1 : count - 1;

        if (count == 0) {
            roleElem.remove();
        } else {
            countElem.html(count);
        }
    } else {
        let newFacet = $(`<button id='${facet_type}-${id}' type="button" class="btn btn-xs btn-default facet-item">`
            + `<input id="${facet_type}-${id}-i" name="${facet_type}s[]" value="${id}" type="hidden" disabled="disabled"/>`
            + `${name} <span class="badge badge-primary">1</span>`
        + '</button>');

        // check if this is the first facet item to be inserted
        if ($(`#${facet_type}-facet-items > .facet-item`).length == 0) {
            $(`#${facet_type}-facet-items`).html(newFacet);

        // if not, append current facet item at the end
        } else {
            $(`#${facet_type}-facet-items`).append("\n").append(newFacet);
        }
    }
}

function clearFacet(facetName) {
    $(`input[name="${facetName}"]`).attr("disabled", "disabled");
    submit_scholar_form();
}

$(document).on('click', '.facet-item', function () {

        let elementId = $(this).attr('id');
        let input = $(`#${elementId}-i`);

        // toggle disabled prop for input
        if (input.attr('disabled')) input.removeAttr('disabled');
        else input.attr('disabled', 'disabled');

        let [facetName, facetId] = elementId.split('-');

        $('#fct_field').val(facetName);

        submit_scholar_form();
});

$(document).ready(function() {

    $('#reading-list-public-switch').click(function(event) {
        var websiteRoot = window.location.origin;

        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var is_public = (event.target.checked) ? 1 : 0;
        var current_reading_list_id = $('#current_reading_list_id').val();

        if(is_public) {
            if(!confirm('You are about to make your reading list publicly accessible through BIP! Scholar’s UI. Are you sure that you want to allow BIP! Scholar to publicly share the papers in your reading list and the connected tags you have assigned?')){
                event.preventDefault();
                return;
            }
        } else {
            if (!confirm('Are you sure that you want to make your reading list private? This will remove access rights to the list to anyone that has saved the URL in the past for future use.')){
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
            success: function() {
            },
            error: function(e) {
                alert("There was an error processing your request!");
            }
        });
    });


    $("#sort-dropdown").on('change', function(){

        // if a reading list was selected, sort the current reading list without exiting from it
        let selected_list_id = $('#scholar-form').attr('data-selected_list_id');
        if (selected_list_id){
            let default_action = $('#scholar-form').attr('action')
            $('#scholar-form').attr('action', default_action + "/" + selected_list_id);

            // do not send input facets in the get request
            $("#scholar-form").find("input").attr('disabled', 'disabled');

        }

        submit_scholar_form();

    });
});
