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
        //debug
        let total = modal.find('.papers-selection-checkbox').length;
        let checkedNow = modal.find('.papers-selection-checkbox:checked').length;
        console.log("DEBUG Select All clicked in modal:", listId);
        console.log("Total checkboxes:", total);
        console.log("Now checked:", checkedNow);

        updateHiddenSelection(listId);
        // Extra DEBUG: dump hidden field content
        console.log("Hidden input value:", $('#selected_papers_' + listId).val());
    });

    // Handle Save button
    $(document).on('click', '.save-selected-works', function () {
        let listId = $(this).data('list-id');
        let selectedPapers = getSelectedPapersArray(listId);

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
            success: function () {
                let url = window.location.pathname;
                let params = new URLSearchParams(window.location.search);

                params.set('list_id', listId);
                params.set(`lists[${listId}][selected_ids]`, selectedPapers.join(','));

                window.location.href = url + '?' + params.toString();
            },error: function (xhr) {
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