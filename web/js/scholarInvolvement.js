// $(document).ready(function () {  
$(window).on('load',function () {

    // change the caret icon of boostrap select
    $('.involvement-region .caret').text("+").toggleClass('caret add-involvement');

    // $('select').selectpicker();
    // $.fn.selectpicker.Constructor.BootstrapVersion = '3';
    $('select.involvement-dropdown').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        // clickedIndex : numeric ascending index of selected item
        // isSelected : states if current item gets selected/unselected (true/false)
        let paperId = $(this).attr('name').split("_")[1];

        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        //Do the required action
        $.ajax({
            url:   window.location.origin + '/bip/web/index.php/scholar/ajaxinvolvement',
            type: 'POST',
            data:
            {
                'involvement_id' : clickedIndex,
                'is_selected' : isSelected,
                'paper_id' : paperId,
                _csrf : csrfToken
            },
            success: function({ involvement_name })
            {
                updateFacet("role", clickedIndex, involvement_name, isSelected);
            },
            error: function(e)
            {
                alert("There was an error processing your request!");
                location.reload();
            }
        });
    });
});