$(document).ready(function () {

    $('select#spaces-ordering, select#spaces-relevance').on("change", function() {
    // Get the selected value of ordering
    let orderingValue = $('select#spaces-ordering').val();

    // when ordering is year, relevance must be low
    if (orderingValue === 'year') {
        $('select#spaces-relevance').val('low');
    }

    });

    $('#spaces-logo_default').change(function() {
        // this will contain a reference to the checkbox
        if ($(this).val() == 1) {
            // the checkbox is now checked
            $("#spaces-logo_upload").attr('disabled','disabled');
            $( "#spaces-logo_upload" ).closest("div").removeClass( "has-success" );
            // hide existing image
            $("#spaces-form_img").hide();
            // remove uploaded file
            $("#spaces-logo_upload").val('');

        } else {
            // the checkbox is now no longer checked
            $("#spaces-logo_upload").removeAttr('disabled');
            $("#spaces-form_img").show();


        }
    });

    $('#spaces-logo_upload').on("change", function(){

        if ($(this).val()) {
            $("#spaces-form_img").hide();
        } else {
            $("#spaces-form_img").show();
        }
    });

});