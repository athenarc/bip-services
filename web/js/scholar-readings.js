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
 * Updates all Facets elements that are linked to this list (same data-linked-list-id).
 * Expects window.bipScholarFacetConfig.softwareRoleIds (string[]) to be set by the page.
 * @param {string} listId - The contributions list element_id (linked_id) for the facet(s) to update
 * @param {string} involvementId - The involvement/role value (0-22)
 * @param {string} involvementName - Display name of the role
 * @param {boolean} selected - true = role added, false = role removed
 */
function updateProfileRoleFacet(listId, involvementId, involvementName, selected) {
    const $containers = $('.js-role-facet-items[data-linked-list-id="' + listId + '"]');
    if (!$containers.length) {
        return;
    }

    const softwareRoleIds = (window.bipScholarFacetConfig && window.bipScholarFacetConfig.softwareRoleIds) || [];

    $containers.each(function () {
        const $container = $(this);
        const $btn = $container.find('input[name="lists[' + listId + '][roles][]"][value="' + involvementId + '"]').closest('button.facet-item');

        if ($btn.length > 0) {
            const $badge = $btn.find('span.badge');
            if (!$badge.length) {
                return;
            }
            let count = parseInt($badge.html(), 10) || 0;
            count = selected ? count + 1 : count - 1;

            if (count <= 0) {
                $btn.remove();
                if ($container.find('.facet-item').length === 0) {
                    $container.html('-');
                }
            } else {
                $badge.html(count);
            }
        } else if (selected) {
            const formId = $('#scholar-form').attr('id') || 'scholar-form';
            const isSoftwareRole = softwareRoleIds.indexOf(String(involvementId)) !== -1;
            const roleIconHtml = isSoftwareRole
                ? "<i class='fa fa-code' aria-hidden='true' title=\"Software contribution role\"></i>\u00A0 "
                : '';
            const labelEsc = $('<div/>').text(involvementName).html();
            const facetSuffix = ($container.attr('id') || '').replace(/^role-facet-items-/, '') || listId;
            const btnId = 'role-' + involvementId + '-facet' + facetSuffix;
            const inputId = btnId + '-i';
            const newBtn = $(
                '<button type="button" class="btn btn-xs btn-default facet-item" id="' + btnId + '" data-list-id="' + String(listId) + '" data-facet="roles">' +
                '<input id="' + inputId + '" name="lists[' + listId + '][roles][]" value="' + involvementId + '" form="' + formId + '" type="hidden" disabled="disabled"/>' +
                roleIconHtml + labelEsc + ' <span class="badge badge-primary">1</span>' +
                '</button>'
            );

            if ($container.is('span') && $container.text().trim() === '-') {
                const containerId = $container.attr('id');
                const $wrapper = $('<div></div>').addClass('js-role-facet-items').attr('data-linked-list-id', listId);
                if (containerId) {
                    $wrapper.attr('id', containerId);
                }
                $wrapper.append(newBtn);
                $container.replaceWith($wrapper);
            } else {
                $container.append('\n').append(newBtn);
            }
        }
    });
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
