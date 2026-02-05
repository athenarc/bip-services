// $(document).ready(function () {
$(window).on('load', () => {
    // change the caret icon of boostrap select
    $('.involvement-region .caret').text('+').toggleClass('caret add-involvement');

    // $('select').selectpicker();
    // $.fn.selectpicker.Constructor.BootstrapVersion = '3';
    $('select.involvement-dropdown').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        // clickedIndex : numeric ascending index of selected item
        // isSelected : states if current item gets selected/unselected (true/false)
        const $dropdown = $(this);
        const paperId = $dropdown.attr('name').split('_')[1];

        const involvementId = $dropdown.find('option').eq(clickedIndex).val();

        // Required for post requests in yii
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Do the required action
        $.ajax({
            url: `${appBaseUrl}/scholar/ajaxinvolvement`,
            type: 'POST',
            data:
            {
                'involvement_id': involvementId,
                'is_selected': isSelected,
                'paper_id': paperId,
                _csrf: csrfToken,
            },
            success: function ({ involvement_name }) {
                // Use the list id linked in the DB
                const $involvementRegion = $dropdown.closest('.involvement-region');
                const listId = $involvementRegion.attr('data-contribution-list-id') != null
                    ? $involvementRegion.attr('data-contribution-list-id')
                    : (function () {
                        const $listContainer = $dropdown.closest('[id^="contributions-list-"]');
                        return $listContainer.length ? $listContainer.attr('id').replace('contributions-list-', '') : null;
                    })();
                if (listId != null && typeof updateProfileRoleFacet === 'function') {
                    updateProfileRoleFacet(listId, involvementId, involvement_name, isSelected);
                }
            },
            error: function (e) {
                alert('There was an error processing your request!');
                location.reload();
            },
        });
    });
});
