$(document).ready(() => {
    $('#search-form').submit(() => {
        if (validateYearsFilters()) {
            $('#author_message').hide();
            $('#results_tbl').hide();
            $('#results_hdr').hide();
            $('#results_ftr').hide();
            $('#results_set').hide();
            $('#top_topics').hide();
            $('#top_annotations').hide();
            $('#summary_panel').hide();
            $('#researcher_panel').hide();
            $('#annotation-expand-controls').hide();
            $('#synonyms').hide();

            // Reset annotation type filter dropdown to 'all' on new search
            $('#annotation_type_filter').val('all');

            $('#loading_results').show();
            return true;
        }

        showYearsErrorMsg();

        return false;
    });

    function validateYearsFilters() {
        const start_year = $('#start_year_input').val();
        const end_year = $('#end_year_input').val();

        return !(start_year && end_year && (end_year < start_year));
    }

    function showYearsErrorMsg() {
        const years_error = $('#years_error').text('\'Start Year\' should be less\n or equal than \'End Year\'!');
        years_error.html(years_error.html().replace(/\n/g, '<br/>'));

        $('#years_form_group').addClass('has-error');
    }


    $('#filterPubmedTypesModal').on('show.bs.modal', () => {
        // read original saved values from hidden field
        let saved = $('#filter-pubmed-types-hidden').val().split(',');
        if (saved.length === 1 && saved[0] === '') { saved = []; }

        // clear all checkboxes
        $('.filter-pubmed-type-checkbox').prop('checked', false);

        // restore saved selections
        saved.forEach(val => {
            $(`.filter-pubmed-type-checkbox[value="${ val }"]`).prop('checked', true);
        });
    });

    // Add selected checkboxes into hidden input and submit the form
    $('#filterApplyPubmedTypes').on('click', () => {
        const selected = [];

        $('.filter-pubmed-type-checkbox:checked').each(function () {
            selected.push($(this).val());
        });

        $('#filter-pubmed-types-hidden').val(selected.join(','));

        $('#filterPubmedTypesModal').modal('hide');

        // Submit search form
        $('#search-form').submit();
    });

    // Handle expand search button click
    $(document).on('click', '.expand-search-btn', function() {
        const expandedKeywords = $(this).data('expanded-keywords');
        
        // Update keywords field (find input with name containing 'keywords' in the search form)
        $('#search-form input[name*="keywords"]').val(expandedKeywords);
        
        // Submit the form (this will trigger the loading indicator)
        $('#search-form').submit();
    });
});
