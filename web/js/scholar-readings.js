function submit_scholar_form() {

    sessionStorage.setItem('scrollPos', $(window).scrollTop());

    $("#loading_results").show();

    $("#publications").hide();
    $("#missing-publications-toggle").hide();
    $("#missing-publications").hide();

    $('#scholar-form input[name="fct_field"], #scholar-form input[name="list_id"]').remove();
    $('#scholar-form input[name^="lists"][name$="[fct_field]"]').each(function () {
        if (!$(this).val()) $(this).remove();
    });

    $("#scholar-form").submit();
}

function ensurePerListFacetField(listId, facet) {
    var $form = $('#scholar-form');
    var name = 'lists[' + listId + '][fct_field]';
    // try by id first (if profile.php pre-renders it)
    var $hid = $form.find('#lists-' + listId + '-fct_field');
    if (!$hid.length) {
        // fallback: find by name or create
        $hid = $form.find('input[name="' + name + '"]');
        if (!$hid.length) {
            $hid = $('<input/>', { type: 'hidden', name: name, id: 'lists-' + listId + '-fct_field' })
                .appendTo($form);
        }
    }
    $hid.val(facet || '');
}

$(document).on('click', '.facet-item', function (e) {
    e.preventDefault();

    var $btn     = $(this);
    var listId   = $btn.data('list-id');
    var elementId= this.id;
    var facet = $btn.data('facet');
    ensurePerListFacetField(listId, facet);

    // Locate the hidden input for this button (id pattern "...-i"; fallback to child query)
    var $inp = $('#' + elementId + '-i');
    if (!$inp.length) {
        $inp = $btn.find('input[type="hidden"]');
    }

    // Toggle only THIS option (multi-select within the same facet group)
    // In markup: disabled => NOT selected; enabled => selected
    var willSelect = $inp.prop('disabled') === true;

    $inp.prop('disabled', !willSelect);
    $btn
      .toggleClass('btn-success', willSelect)
      .toggleClass('btn-default', !willSelect)
      .attr('aria-pressed', willSelect ? 'true' : 'false');

    submit_scholar_form();
});

function clearFacet(listId, facetName) {
    // Map facet → prefix used in DOM IDs
    var prefixMap = {
        topics: "topic",
        roles: "role",
        accesses: "access",
        types: "type"
    };
    var facetIdPrefix = prefixMap[facetName] || facetName;

    // Reset: show all, disable all inputs, remove selected styling
    $('#'+facetIdPrefix+'-facet-items-'+listId+' .facet-item')
        .show()
        .find('input').prop('disabled', true)
        .end()
        .removeClass('btn-success')
        .addClass('btn-default')
        .attr('aria-pressed', 'false');
    
    ensurePerListFacetField(listId, '');
    submit_scholar_form();
}

$(document).ready(function () {
    var scrollPos = sessionStorage.getItem('scrollPos');
    if (scrollPos !== null) {
        $(window).scrollTop(scrollPos);
    }

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
        var selected_list_id = $('#scholar-form').attr('data-selected_list_id');
        if (selected_list_id){
            var default_action = $('#scholar-form').attr('action');
            $('#scholar-form').attr('action', default_action + "/" + selected_list_id);
            $("#scholar-form").find("input").attr('disabled', 'disabled');
            $('#active_list_id, [id^="lists-"][id$="-fct_field"], input[name="fct_field"]').prop('disabled', false);
        }
        submit_scholar_form();
    });
});