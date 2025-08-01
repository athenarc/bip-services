console.log('papersselection.js is loaded ✅');
$(document).ready(function () {

    function getSelectedPapersArray(listId) {
        let raw = $('#selected_papers_' + listId).val();
        return raw ? raw.split(',').filter(str => str !== '') : [];
    }

    function updateHiddenSelection(listId) {
        let selected = [];
        $('#select-works-modal-' + listId + ' .papers-selection-checkbox:checked').each(function () {
            selected.push($(this).data('key').toString());
        });
        $('#selected_papers_' + listId).val(selected.join(','));
    }

    function checkSavedSelection(listId) {
        let selection = getSelectedPapersArray(listId);

        $('#select-works-modal-' + listId + ' .papers-selection-checkbox').each(function () {
            let key = $(this).data('key').toString();
            $(this).prop('checked', selection.includes(key));
        });

        // update select-all state
        let total = $('#select-works-modal-' + listId + ' .papers-selection-checkbox').length;
        let checked = $('#select-works-modal-' + listId + ' .papers-selection-checkbox:checked').length;
        $('#select-works-modal-' + listId + ' .papers-select-on-check-all').prop('checked', total > 0 && total === checked);
    }

    // Handle single checkbox toggle
    $(document).on('change', '.papers-selection-checkbox', function () {
        let listId = $(this).closest('.modal').attr('id').replace('select-works-modal-', '');
        updateHiddenSelection(listId);
        checkSavedSelection(listId);
    });

    // Handle select-all toggle
    $(document).on('change', '.papers-select-on-check-all', function () {
        let modal = $(this).closest('.modal');
        let listId = modal.attr('id').replace('select-works-modal-', '');
        let checked = $(this).is(':checked');
        modal.find('.papers-selection-checkbox').prop('checked', checked);
        updateHiddenSelection(listId);
    });

    // Handle Save button
    $(document).on('click', '.save-selected-works', function () {
        let listId = $(this).data('list-id');
        let selectedPapers = getSelectedPapersArray(listId);

        console.log('Save button clicked for listId:', listId);
        console.log('Selected papers:', selectedPapers);

        if (selectedPapers.length === 0) {
            alert('Please select at least one work.');
            return;
        }

        $('#select-works-modal-' + listId).modal('hide');

        $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: {
                list_id: listId,
                lists: {
                    [listId]: {
                        selected_ids: selectedPapers
                    }
                }
            },
            success: function (response) {
                console.log('AJAX success - updating list');
                $('#contributions-list-' + listId).html(response);
            },
            error: function (xhr) {
                console.error('AJAX error status:', xhr.status);
                console.error('AJAX error response:', xhr.responseText);
                alert('Error ' + xhr.status + ' — check console for details.');
            }

        });
    });

    // Restore saved selection when modal opens
    $('.modal').on('shown.bs.modal', function () {
        let listId = $(this).attr('id').replace('select-works-modal-', '');
        checkSavedSelection(listId);
    });
});