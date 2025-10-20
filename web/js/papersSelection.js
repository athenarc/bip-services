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

    updateSelectAllLabel(listId);
    updateSelectionCounter(listId);
  }

  // read max from the modal
  function getMaxAllowed(listId) {
    let modal = $('#select-works-modal-' + listId);
    let max = modal.data('max');
    if (max === '' || typeof max === 'undefined' || max === null) return null;
    max = parseInt(max, 10);
    return Number.isFinite(max) && max >= 0 ? max : null;
  }

  // central: enable/disable checkboxes & "select all" based on max
  function enforceMaxState($modal) {
    let listId = $modal.attr('id').replace('select-works-modal-', '');
    let max = getMaxAllowed(listId);
    let $boxes = $modal.find('.papers-selection-checkbox');
    let $unchecked = $boxes.not(':checked');
    let $selectAll = $modal.find('.papers-select-on-check-all');

    // no limit → everything enabled
    if (max === null) {
      $unchecked.prop('disabled', false);
      $selectAll.prop('disabled', false);
      return;
    }

    let total = $boxes.length;
    let checkedCount = $modal.find('.papers-selection-checkbox:checked').length;

    // select-all is disabled if max < total (since it cannot truly check all)
    if (max < total) {
      $selectAll.prop('disabled', true).prop('checked', false);
    } else {
      $selectAll.prop('disabled', false);
    }

    // if already at max, lock remaining unchecked boxes
    if (checkedCount >= max) {
      $unchecked.prop('disabled', true);
    } else {
      $unchecked.prop('disabled', false);
    }
  }

  function updateSelectAllLabel(listId) {
    var $modal = $('#select-works-modal-' + listId);
    var total   = $modal.find('.papers-selection-checkbox').length;
    var checked = $modal.find('.papers-selection-checkbox:checked').length;
    var $label  = $modal.find('.select-all-toggle-label');

    // If all currently visible items are checked -> "Deselect All", else "Select All"
    var text = (total > 0 && checked === total) ? 'Deselect All' : 'Select All';
    $label.text(text);
  }

  function updateSelectionCounter(listId) {
    var max = getMaxAllowed(listId);
    var $badge = $('#selection-counter-' + listId);

    // Show only if there is a max limit
    if (max === null) { 
      $badge.hide();
      return;
    }

    var selected = $('#select-works-modal-' + listId + ' .papers-selection-checkbox:checked').length;
    $badge.text(selected + '/' + max + ' selections').show();
  }

  function reconcileWithMax(listId) {
    var max = getMaxAllowed(listId);
    if (max === null) return;

    var $modal  = $('#select-works-modal-' + listId);
    var $checked = $modal.find('.papers-selection-checkbox:checked');

    if ($checked.length > max) {
      // keep first 'max' (by DOM order), uncheck the rest
      $checked.each(function(i) {
        if (i >= max) $(this).prop('checked', false);
      });
      updateHiddenSelection(listId);
    }

    // refresh header UI
    checkSavedSelection(listId);   // updates select-all + label
    enforceMaxState($modal);
    updateSelectionCounter(listId);
  }


  // single checkbox toggle
  $(document).on('change', '.papers-selection-checkbox', function () {
    let $modal = $(this).closest('.modal');
    let listId = $modal.attr('id').replace('select-works-modal-', '');
    let max = getMaxAllowed(listId);

    // if checking this would exceed max, just refuse the change (no alerts)
    if (max !== null && $(this).is(':checked')) {
      let checkedCount = $modal.find('.papers-selection-checkbox:checked').length;
      if (checkedCount > max) {
        $(this).prop('checked', false);
        return;
      }
    }

    updateHiddenSelection(listId);
    checkSavedSelection(listId);
    enforceMaxState($modal);
    updateSelectionCounter(listId);
  });

  // select-all toggle (respect max; disable extras)
  $(document).on('change', '.papers-select-on-check-all', function () {
    let $modal = $(this).closest('.modal');
    let listId = $modal.attr('id').replace('select-works-modal-', '');
    let checked = $(this).is(':checked');
    let max = getMaxAllowed(listId);
    let $boxes = $modal.find('.papers-selection-checkbox');

    if (checked) {
      if (max !== null && $boxes.length > max) {
        // check only up to max
        $boxes.each(function (i) {
          $(this).prop('checked', i < max);
        });
      } else {
        $boxes.prop('checked', true);
      }
    } else {
      $boxes.prop('checked', false);
    }

    updateHiddenSelection(listId);
    checkSavedSelection(listId);
    enforceMaxState($modal);
    updateSelectAllLabel(listId);
    updateSelectionCounter(listId);
  });

  // save button (no alerts; just rely on disabled state)
  $(document).on('click', '.save-selected-works', function () {
    let listId = $(this).data('list-id');
    let selectedPapers = getSelectedPapersArray(listId);
    let max = getMaxAllowed(listId);

    if (max !== null && selectedPapers.length > max) {
      selectedPapers = selectedPapers.slice(0, max);
    }

    $('#select-works-modal-' + listId).modal('hide');

    $.ajax({
      url: '/dbip/web/scholar/save-selected-works',
      type: 'POST',
      data: {
        list_id: listId,
        paper_ids: selectedPapers,
        _csrf: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.status === 'success') {
          location.reload();
        } 
      },
      error: function (xhr) {
        console.error('AJAX error', xhr.status, xhr.responseText);
      }
    });
  });

  // when modal opens: inject max and enforce states
  $('.modal').on('shown.bs.modal', function (e) {
    let $modal = $(this);
    let listId = $modal.attr('id').replace('select-works-modal-', '');

    if (e && e.relatedTarget) {
      let maxFromButton = $(e.relatedTarget).data('max');
      $modal.data('max', maxFromButton);
    }

    checkSavedSelection(listId);
    enforceMaxState($modal);
    updateSelectAllLabel(listId);
    updateSelectionCounter(listId);
    reconcileWithMax(listId);
  });
});