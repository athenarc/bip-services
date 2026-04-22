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

const FACET_PREVIEW_LIMIT = 10;
const FACET_EXPAND_STATE_KEY = 'bipFacetExpandState';
const FACET_ITEM_HIDDEN_CLASS = 'facet-item-hidden';

function getFacetExpandState() {
    try {
        const raw = sessionStorage.getItem(FACET_EXPAND_STATE_KEY);
        return raw ? JSON.parse(raw) : {};
    } catch (err) {
        return {};
    }
}

function saveFacetExpandState(state) {
    try {
        sessionStorage.setItem(FACET_EXPAND_STATE_KEY, JSON.stringify(state));
    } catch (err) {
        // Ignore storage failures (private mode, disabled storage).
    }
}

function getFacetGroupKey($row, $container) {
    const containerId = $container.attr('id');
    const fallback = $row.find('.facet-header strong').text().trim() || 'facet-group';
    return `${ window.location.pathname }::${ containerId || fallback }`;
}

function isFacetButtonSelected($button) {
    const $input = $button.find('input[type="hidden"]');
    return $input.length ? !$input.prop('disabled') : $button.hasClass('btn-success');
}

function applyFacetRowPreview($row) {
    const $container = $row.find('[id*="facet-items"]').first();
    if (!$container.length) return;

    const $buttons = $container.find('.facet-item');
    const $toggle = $row.find('.js-facet-see-more').first();
    if ($buttons.length <= FACET_PREVIEW_LIMIT) {
        $buttons.removeClass(FACET_ITEM_HIDDEN_CLASS);
        if ($toggle.length) {
            $toggle.remove();
        }
        return;
    }

    const state = getFacetExpandState();
    const key = getFacetGroupKey($row, $container);
    const isExpanded = !!state[key];

    $buttons.each(function (index, buttonEl) {
        const $button = $(buttonEl);
        const shouldShow = isExpanded || index < FACET_PREVIEW_LIMIT || isFacetButtonSelected($button);
        $button.toggleClass(FACET_ITEM_HIDDEN_CLASS, !shouldShow);
    });

    const label = isExpanded ? 'See less' : 'See more';
    if ($toggle.length) {
        $toggle.text(label).attr('aria-expanded', isExpanded ? 'true' : 'false');
    } else {
        const $newToggle = $(`<button type="button" class="btn btn-xs js-facet-see-more facet-see-more-btn grey-link fs-inherit" aria-expanded="${ isExpanded ? 'true' : 'false' }">${ label }</button>`);
        $container.after($newToggle);
    }
}

function initializeFacetPreview() {
    $('.facet-row').each(function () {
        applyFacetRowPreview($(this));
    });
}

$(document).on('click', '.js-facet-see-more', function (e) {
    e.preventDefault();

    const $toggle = $(this);
    const $row = $toggle.closest('.facet-row');
    const $container = $row.find('[id*="facet-items"]').first();
    if (!$container.length) return;

    const key = getFacetGroupKey($row, $container);
    const state = getFacetExpandState();
    state[key] = !state[key];
    saveFacetExpandState(state);

    applyFacetRowPreview($row);
});

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
    // Support both signatures:
    // - clearFacet('topics[]') for Readings
    // - clearFacet(listId, 'topics') for Scholar profile
    const hasListContext = typeof facetName !== 'undefined';
    const normalizedListId = hasListContext ? listId : null;
    const facetNameRaw = hasListContext ? facetName : listId;
    const normalizedFacetName = String(facetNameRaw || '').replace(/\[\]$/, '');

    // Map facet → prefix used in DOM IDs
    const prefixMap = {
        topics: 'topic',
        roles: 'role',
        accesses: 'access',
        types: 'type',
        tags: 'tag',
        rd_status: 'rd_status',
    };
    const facetIdPrefix = prefixMap[normalizedFacetName] || normalizedFacetName;
    let $buttons = $();

    if (hasListContext) {
        // Scholar profile: prefer semantic selectors by list + facet.
        $buttons = $(`.facet-item[data-list-id="${normalizedListId}"][data-facet="${normalizedFacetName}"]`);

        // Fallback for role facet variants (container id may use facet element id).
        if (!$buttons.length && normalizedFacetName === 'roles') {
            $buttons = $(`.js-role-facet-items[data-linked-list-id="${normalizedListId}"] .facet-item`);
        }

        // Generic fallback using container ID pattern.
        if (!$buttons.length) {
            $buttons = $(`#${facetIdPrefix}-facet-items-${normalizedListId} .facet-item`);
        }
    } else {
        // Readings page: clear by hidden-input name.
        $buttons = $(`input[name="${normalizedFacetName}[]"]`).closest('.facet-item');

        // Generic fallback using container ID pattern.
        if (!$buttons.length) {
            $buttons = $(`#${facetIdPrefix}-facet-items .facet-item`);
        }
    }

    // Reset: show all, disable all inputs, remove selected styling.
    $buttons
        .show()
        .find('input').prop('disabled', true)
        .end()
        .removeClass('btn-success')
        .addClass('btn-default')
        .attr('aria-pressed', 'false');

    if (hasListContext) {
        ensurePerListFacetField(normalizedListId, '');
    } else {
        $('#fct_field').val('');
    }

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

    const $facetRow = $(`#${facet_type}-facet-items`).closest('.facet-row');
    if ($facetRow.length) {
        applyFacetRowPreview($facetRow);
    }
}

/**
 * Update the role facet for a specific contributions list on the scholar profile.
 * Expects window.bipScholarFacetConfig.softwareRoleIds (string[]).
 */
function updateProfileRoleFacet(listId, involvementId, involvementName, selected) {
    const $containers = $(`.js-role-facet-items[data-linked-list-id="${ listId }"]`);
    if (!$containers.length) return;

    const softwareRoleIds = (window.bipScholarFacetConfig && window.bipScholarFacetConfig.softwareRoleIds) || [];
    const isSoftwareRole = softwareRoleIds.indexOf(String(involvementId)) !== -1;
    const roleIconHtml = isSoftwareRole ? "<i class='fa fa-code' aria-hidden='true' title=\"Software contribution role\"></i>\u00A0 " : '';
    const formId = $('#scholar-form').attr('id') || 'scholar-form';
    const labelEsc = $('<div/>').text(involvementName).html();

    $containers.each(function () {
        const $container = $(this);
        const $btn = $container.find(`input[name="lists[${ listId }][roles][]"][value="${ involvementId }"]`).closest('button.facet-item');

        if ($btn.length) {
            const $badge = $btn.find('span.badge');
            if (!$badge.length) return;

            let count = parseInt($badge.html(), 10) || 0;
            count = selected ? count + 1 : count - 1;

            if (count <= 0) {
                $btn.remove();
                if (!$container.find('.facet-item').length) $container.html('-');
            } else {
                $badge.html(count);
            }
        } else if (selected) {
            const facetSuffix = ($container.attr('id') || '').replace(/^role-facet-items-/, '') || listId;
            const newBtn = $(
                `<button type="button" class="btn btn-xs btn-default facet-item" id="role-${ involvementId }-facet${ facetSuffix }" data-list-id="${ listId }" data-facet="roles">` +
                `<input id="role-${ involvementId }-facet${ facetSuffix }-i" name="lists[${ listId }][roles][]" value="${ involvementId }" form="${ formId }" type="hidden" disabled="disabled"/>` +
                `${ roleIconHtml }${ labelEsc } <span class="badge badge-primary">1</span></button>`
            );

            if ($container.text().trim() === '-') {
                if ($container.is('span')) {
                    const $wrapper = $('<div></div>').addClass('js-role-facet-items').attr('data-linked-list-id', listId);
                    const id = $container.attr('id');
                    if (id) $wrapper.attr('id', id);
                    $container.replaceWith($wrapper.append(newBtn));
                } else {
                    $container.empty().append(newBtn);
                }
            } else {
                $container.append('\n').append(newBtn);
            }
        }

        const $facetRow = $container.closest('.facet-row');
        if ($facetRow.length) {
            applyFacetRowPreview($facetRow);
        }
    });
}

$(document).ready(() => {
    initializeFacetPreview();

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
