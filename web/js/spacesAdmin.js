$(document).ready(() => {
    $('select#spaces-ordering, select#spaces-relevance').on('change', () => {
    // Get the selected value of ordering
        const orderingValue = $('select#spaces-ordering').val();

        // when ordering is year, relevance must be low
        if (orderingValue === 'year') {
            $('select#spaces-relevance').val('low');
        }
    });

    $('#spaces-logo_default').change(function () {
        // this will contain a reference to the checkbox
        if ($(this).val() == 1) {
            // the checkbox is now checked
            $('#spaces-logo_upload').attr('disabled', 'disabled');
            $('#spaces-logo_upload').closest('div').removeClass('has-success');
            // hide existing image
            $('#spaces-form_img').hide();
            // remove uploaded file
            $('#spaces-logo_upload').val('');
        } else {
            // the checkbox is now no longer checked
            $('#spaces-logo_upload').removeAttr('disabled');
            $('#spaces-form_img').show();
        }
    });

    $('#spaces-logo_upload').on('change', function () {
        if ($(this).val()) {
            $('#spaces-form_img').hide();
        } else {
            $('#spaces-form_img').show();
        }
    });

    // show button only if has_pubmed_types is checked
    $('#spaces-has_pubmed_types').on('change', function () {
        if ($(this).is(':checked')) {
            $('#spaces-pubmed-types-container').show();
        } else {
            $('#spaces-pubmed-types-container').hide();
            $('#spaces-pubmed-types-hidden').val('');
            // clear checkboxes inside modal
            $('.spaces-pubmed-type-checkbox').prop('checked', false);
            // Reset counter
            updatePubmedCounter();
        }
    }).trigger('change');


    $('#spacesPubmedTypesModal').on('show.bs.modal', () => {
        // read original saved values from hidden field
        let saved = $('#spaces-pubmed-types-hidden').val().split(',');
        if (saved.length === 1 && saved[0] === '') { saved = []; }

        // clear all checkboxes
        $('.spaces-pubmed-type-checkbox').prop('checked', false);

        // restore saved selections
        saved.forEach(val => {
            $(`.spaces-pubmed-type-checkbox[value="${ val }"]`).prop('checked', true);
        });
    });

    // Save selected checkboxes into hidden input
    $('#spacesSavePubmedTypes').on('click', () => {
        const selected = [];

        $('.spaces-pubmed-type-checkbox:checked').each(function () {
            selected.push($(this).val());
        });

        $('#spaces-pubmed-types-hidden').val(selected.join(','));
        updatePubmedCounter();
        $('#spacesPubmedTypesModal').modal('hide');
    });

    function updatePubmedCounter() {
        const count = $('.spaces-pubmed-type-checkbox:checked').length;
        $('#spaces-pubmed-types-count').text(count);
    }

    // When has_annotations_flag is unchecked, disable and uncheck enable_annotations_flag
    $('#spaces-has_annotations_flag').on('change', function () {
        $('#spaces-enable_annotations_flag')
            .prop('disabled', !this.checked)
            .prop('checked', false);
    });

    // Handle perform_search_expansion checkbox for each annotation item
    function toggleExpansionField(checkbox) {
        const $checkbox = $(checkbox);
        const $item = $checkbox.closest('.item');
        const $expansionField = $item.find('.expansion-field-input');
        
        if ($checkbox.is(':checked')) {
            $expansionField.prop('disabled', false);
            // Trigger validation for the expansion field
            $expansionField.trigger('blur');
        } else {
            $expansionField.prop('disabled', true).val('');
            // Clear validation errors when disabled
            const $field = $expansionField.closest('.form-group');
            $field.removeClass('has-error');
            $field.find('.help-block').remove();
        }
    }

    // Initialize expansion fields on page load
    $('.item').each(function() {
        const $item = $(this);
        const $checkbox = $item.find('input[type="checkbox"][name*="[perform_search_expansion]"]');
        if ($checkbox.length) {
            toggleExpansionField($checkbox[0]);
        }
    });

    // Handle checkbox changes (including dynamically added items)
    $(document).on('change', 'input[type="checkbox"][name*="[perform_search_expansion]"]', function() {
        toggleExpansionField(this);
    });

    // Make "Perform search expansion" labels bold
    function makeExpansionLabelsBold() {
        $('input[type="checkbox"][name*="[perform_search_expansion]"]').each(function() {
            const $checkbox = $(this);
            const $label = $checkbox.closest('.checkbox').find('label');
            if ($label.length && !$label.hasClass('bold-label')) {
                $label.css('font-weight', 'bold').addClass('bold-label');
            }
        });
    }

    // Initialize on page load
    makeExpansionLabelsBold();

    // Also handle dynamically added items using MutationObserver
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function() {
            makeExpansionLabelsBold();
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});
