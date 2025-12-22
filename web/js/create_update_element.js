$(document).ready(() => {
    $('.opt-checkbox').prop('disabled', true);

    let initialSelectedType = $('#elements-type').val();
    if (initialSelectedType != null) {
        initialSelectedType = initialSelectedType.replace(' ', '-');
        $('.element-type-section').hide();
        $(`#element-type-${ initialSelectedType}`).show();
    }

    $('#elements-type').change(function () {
        const selectedType = $(this).val().replace(' ', '-');
        $('.element-type-section').hide();
        $(`#element-type-${ selectedType}`).show();
    });

    $('#selectDeselectButton').click(() => {
        const checkboxes = $('input[type="checkbox"]');
        checkboxes.prop('checked', !checkboxes.prop('checked'));
    });

    const facetTypes = ['topics', 'roles', 'availability', 'work-type'];

    facetTypes.forEach(facet => {
        const facetClass = `.${ facet }-checkbox`;
        const facetOptClass = `.${ facet }-opt-checkbox`;
        const checkboxOptsGroup = $(facetOptClass);

        if ($(facetClass).is(':checked')) {
            checkboxOptsGroup.prop('disabled', false);
        } else {
            checkboxOptsGroup.prop('disabled', true);
        }

        $(facetClass).change(function () {
            if ($(this).is(':checked')) {
                checkboxOptsGroup.prop('disabled', false);
            } else {
                checkboxOptsGroup.prop('disabled', true);
                checkboxOptsGroup.prop('checked', false);
            }
        });
    });

    // Function to initialize the hidden inputs
    function initializeOrderInputs() {
        const initialSemanticsOrder = [];
        $('#semantics-container .semantics-group').each(function () {
            const semantics = $(this).find('.semantics-heading h4').text().trim();
            if (semantics) {
                initialSemanticsOrder.push(semantics);
            }
        });
        $('#semantics-order').val(JSON.stringify(initialSemanticsOrder));

        logFullIndicatorOrder();
    }

    // Function to log the full indicator order
    function logFullIndicatorOrder() {
        const fullIndicatorOrder = [];
        $('#semantics-container .semantics-group').each(function () {
            const semanticsGroup = $(this).find('.semantics-heading').text().trim();
            $(this).find('.indicator-container .indicator-item').each(function () {
                const indicatorId = $(this).attr('id').replace('indicator-', '');
                fullIndicatorOrder.push({ semantics: semanticsGroup, indicator: indicatorId });
            });
        });

        const indicatorOrderArray = fullIndicatorOrder.map(entry => entry.indicator);

        $('#indicator-order').val(JSON.stringify(indicatorOrderArray));
    }

    // Initialize hidden inputs on page load
    initializeOrderInputs();

    // Make the semantics groups sortable
    $('#semantics-container').sortable({
        handle: '.semantics-heading',
        cursor: 'move',
        update: function (event, ui) {
            let order = $(this).sortable('toArray', { attribute: 'id' }).map(id => id.replace('semantics-', '').replace(/-/g, ' '));
            order = order.splice(3, 6);
            $('#semantics-order').val(JSON.stringify(order));
            logFullIndicatorOrder();
        },
    });

    // Make the indicator items sortable
    $('#semantics-container .indicator-container').sortable({
        handle: '.indicator-heading',
        axis: 'y',
        containment: 'parent',
        cursor: 'move',
        update: function (event, ui) {
            logFullIndicatorOrder();
        },
    });

    // Collapse/Expand functionality
    $('.toggle-indicators').click(function () {
        const $button = $(this);
        const $indicators = $button.closest('.semantics-group').find('.indicator-container');
        $indicators.toggle();
        $button.text($indicators.is(':visible') ? 'Collapse' : 'Expand');
    });

    // Collapse All/Expand All functionality
    $('.toggle-all-indicators').click(function (event) {
        event.preventDefault(); // Prevent default action
        const $button = $(this);
        const $indicators = $('.indicator-container');
        const isVisible = $indicators.is(':visible');
        $indicators.toggle(!isVisible);
        $('.toggle-indicators').text(isVisible ? 'Expand' : 'Collapse');
        $button.text(isVisible ? 'Expand All' : 'Collapse All');
    });

    // Global combobox functionality
    $('.global-status-combobox').change(function () {
        const status = $(this).val();
        $('.indicator-container select').val(status).change();
    });

    // Individual group combobox functionality
    $('.group-status-combobox').change(function () {
        const status = $(this).val();
        $(this).closest('.semantics-group').find('.indicator-container select').val(status).change();
    });
    (function ($) {
    // ---------- Helpers ----------
        function bool(v) { return !!v; }

        function toggleHeaderSize() {
            const on = $('#elementcontributions-show_header').is(':checked');
            $('#contrib-heading-wrap').toggle(on);
        }

        function togglePagination() {
            const on = $('#elementcontributions-show_pagination').is(':checked');
            $('#contrib-pagesize-wrap').toggle(on);
            $('#elementcontributions-page_size').prop('disabled', !on);
            if (!on) { $('#elementcontributions-page_size').val(''); }
        }

        function toggleTopKUI() {
            const on = $('#contrib-use-topk').is(':checked');
            $('#contrib-topk-wrap').toggle(on);
            $('#elementcontributions-top_k').prop('disabled', !on);
            if (!on) { $('#elementcontributions-top_k').val(''); }
        }

        function toggleUserDefinedMax() {
            const on = $('#contrib-user-defined').is(':checked');
            const $group = $('#contrib-user-defined-max').closest('.form-group');
            $group.toggle(on);
            $('#contrib-user-defined-max').prop('disabled', !on);
            if (!on) { $('#contrib-user-defined-max').val(''); }
        }

        // Mutual exclusion: Top-K  <->  Researcher selection
        function enforceMutualExclusion(from) {
            const topkOn = $('#contrib-use-topk').is(':checked');
            const userSelOn = $('#contrib-user-defined').is(':checked');

            if (from === 'topk' && topkOn) {
                // disable Researcher selection
                $('#contrib-user-defined').prop('checked', false).prop('disabled', true).trigger('change');
            } else if (from === 'usersel' && userSelOn) {
                // disable Top-K
                $('#contrib-use-topk').prop('checked', false).prop('disabled', true).trigger('change');
            }

            // re-enable the opposite if this one is OFF
            if (!topkOn) { $('#contrib-user-defined').prop('disabled', false); }
            if (!userSelOn) { $('#contrib-use-topk').prop('disabled', false); }
        }

        // ---------- Init (on load) ----------
        $(document).ready(() => {
        // Initial visibility states
            toggleHeaderSize();
            togglePagination();
            toggleUserDefinedMax();

            // Auto-check Top-K checkbox if K already has a value
            const kVal = $('#elementcontributions-top_k').val();
            $('#contrib-use-topk').prop('checked', bool(kVal));
            toggleTopKUI();

            // If user_defined is on at load, mutually exclude Top-K
            enforceMutualExclusion();

            // ---------- Events ----------
            $(document).on('change', '#elementcontributions-show_header', toggleHeaderSize);
            $(document).on('change', '#elementcontributions-show_pagination', togglePagination);

            $(document).on('change', '#contrib-use-topk', () => {
                toggleTopKUI();
                enforceMutualExclusion('topk');
            });

            $(document).on('change', '#contrib-user-defined', () => {
                toggleUserDefinedMax();
                enforceMutualExclusion('usersel');
            });

            // Before submit, clear values of disabled/hidden fields
            $('#element-form').on('submit', () => {
                if (!$('#contrib-use-topk').is(':checked')) {
                    $('#elementcontributions-top_k').val('');
                }
                if (!$('#elementcontributions-show_pagination').is(':checked')) {
                    $('#elementcontributions-page_size').val('');
                }
                if (!$('#elementcontributions-show_header').is(':checked')) {
                    // keep heading_type if you want; or blank it:
                    // $('#contrib-heading-size').val('');
                }
                if (!$('#contrib-user-defined').is(':checked')) {
                    $('#contrib-user-defined-max').val('');
                }
            });
        });
    })(jQuery);
});
