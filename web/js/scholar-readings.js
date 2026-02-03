function submit_scholar_form() {
    $('#loading_results').show();

    $('#publications').hide();
    $('#missing-publications-toggle').hide();
    $('#missing-publications').hide();

    $('#scholar-form input[name="fct_field"], #scholar-form input[name="list_id"]').remove();
    $('#scholar-form input[name^="lists"][name$="[fct_field]"]').each(function () {
        if (!$(this).val()) { $(this).remove(); }
    });

    $('#scholar-form').submit();
}

function ensurePerListFacetField(listId, facet) {
    const $form = $('#scholar-form');
    const name = `lists[${ listId }][fct_field]`;
    // try by id first (if profile.php pre-renders it)
    let $hid = $form.find(`#lists-${ listId }-fct_field`);
    if (!$hid.length) {
        // fallback: find by name or create
        $hid = $form.find(`input[name="${ name }"]`);
        if (!$hid.length) {
            $hid = $('<input/>', { type: 'hidden', name: name, id: `lists-${ listId }-fct_field` })
                .appendTo($form);
        }
    }
    $hid.val(facet || '');
}

$(document).on('click', '.facet-item', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const listId = $btn.data('list-id');
    const elementId = this.id;
    const facet = $btn.data('facet');
    ensurePerListFacetField(listId, facet);

    // Locate the hidden input for this button (id pattern "...-i"; fallback to child query)
    let $inp = $(`#${ elementId }-i`);
    if (!$inp.length) {
        $inp = $btn.find('input[type="hidden"]');
    }

    // Toggle only THIS option (multi-select within the same facet group)
    // In markup: disabled => NOT selected; enabled => selected
    const willSelect = $inp.prop('disabled') === true;

    $inp.prop('disabled', !willSelect);
    $btn
        .toggleClass('btn-success', willSelect)
        .toggleClass('btn-default', !willSelect)
        .attr('aria-pressed', willSelect ? 'true' : 'false');

    submit_scholar_form();
});

function clearFacet(listId, facetName) {
    // Map facet → prefix used in DOM IDs
    const prefixMap = {
        topics: 'topic',
        roles: 'role',
        accesses: 'access',
        types: 'type',
    };
    const facetIdPrefix = prefixMap[facetName] || facetName;

    // Reset: show all, disable all inputs, remove selected styling
    $(`#${facetIdPrefix}-facet-items-${listId} .facet-item`)
        .show()
        .find('input').prop('disabled', true)
        .end()
        .removeClass('btn-success')
        .addClass('btn-default')
        .attr('aria-pressed', 'false');

    ensurePerListFacetField(listId, '');
    submit_scholar_form();
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

/**
 * Update the role facet for a specific contributions list on the scholar profile.
 * Called when the user adds or removes a contribution role from a paper.
 * Only the facet linked to the list containing that paper is updated.
 * @param {string} listId - The contributions list element_id (linked_id) for the facet to update
 * @param {string} involvementId - The involvement/role value (0-13)
 * @param {string} involvementName - Display name of the role
 * @param {boolean} selected - true = role added, false = role removed
 */
function updateProfileRoleFacet(listId, involvementId, involvementName, selected) {
    const containerId = 'role-facet-items-' + listId;
    const buttonId = 'role-' + involvementId + '-list' + listId;
    const $container = $('#' + containerId);
    if (!$container.length) {
        return;
    }

    const $btn = $container.find('#' + buttonId);

    if ($btn.length > 0) {
        const $badge = $btn.find('span.badge');
        if (!$badge.length) {
            return;
        }
        let count = parseInt($badge.html(), 10) || 0;
        count = selected ? count + 1 : count - 1;

        if (count <= 0) {
            $btn.remove();
            // If no facet buttons left, show placeholder
            if ($container.find('.facet-item').length === 0) {
                $container.html('-');
            }
        } else {
            $badge.html(count);
        }
    } else if (selected) {
        const formId = $('#scholar-form').attr('id') || 'scholar-form';
        const newBtn = $('<button></button>')
            .attr('id', buttonId)
            .attr('type', 'button')
            .addClass('btn btn-xs btn-default facet-item')
            .attr('data-list-id', listId)
            .attr('data-facet', 'roles')
            .append($('<input>').attr({
                id: buttonId + '-i',
                name: 'lists[' + listId + '][roles][]',
                value: involvementId,
                form: formId,
                type: 'hidden',
                disabled: 'disabled'
            }))
            .append(document.createTextNode(' ' + involvementName + ' '))
            .append($('<span></span>').addClass('badge badge-primary').text('1'));

        if ($container.is('span') && $container.text().trim() === '-') {
            const $wrapper = $('<div></div>').attr('id', containerId).append(newBtn);
            $container.replaceWith($wrapper);
        } else {
            $container.append('\n').append(newBtn);
        }
    }
}

$(document).ready(() => {
    $('#reading-list-public-switch').click(event => {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const is_public = (event.target.checked) ? 1 : 0;
        const current_reading_list_id = $('#current_reading_list_id').val();

        if (is_public) {
            if (!confirm('You are about to make your reading list publicly accessible through BIP! Scholar’s UI. Are you sure?')) {
                event.preventDefault();
                return;
            }
        } else {
            if (!confirm('Are you sure you want to make your reading list private?')) {
                event.preventDefault();
                return;
            }
        }

        $.ajax({
            url: `${appBaseUrl}/readings/ajax-update-public-reading-list`,
            type: 'POST',
            data: {
                'is_public': is_public,
                'reading_list_id': current_reading_list_id,
                _csrf: csrfToken,
            },
            error: function () {
                alert('There was an error processing your request!');
            },
        });
    });

    $(document).on('change', '[id^="sort-dropdown-"]', () => {
        const selected_list_id = $('#scholar-form').attr('data-selected_list_id');
        if (selected_list_id) {
            const default_action = $('#scholar-form').attr('action');
            $('#scholar-form').attr('action', `${default_action }/${ selected_list_id}`);
            $('#scholar-form').find('input').attr('disabled', 'disabled');
            $('#active_list_id, [id^="lists-"][id$="-fct_field"], input[name="fct_field"]').prop('disabled', false);
        }
        submit_scholar_form();
    });
});
