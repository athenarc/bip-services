$(document).ready(function()
{
    $('#search-form').submit(function() {

        if (validateYearsFilters()) {
            $("#author_message").hide();
            $("#results_tbl").hide();
            $("#results_hdr").hide();
            $("#results_ftr").hide();
            $("#results_set").hide();
            $("#top_topics").hide();
            $("#summary_panel").hide();
            $("#researcher_panel").hide();

            $("#loading_results").show();
            return true;
        }

        showYearsErrorMsg();

        return false;
    
    });

    function validateYearsFilters() {
        let start_year = $('#start_year_input').val();
        let end_year = $('#end_year_input').val();
      
        return !(start_year && end_year && (end_year < start_year));
    }

    function showYearsErrorMsg() {
        let years_error = $('#years_error').text('\'Start Year\' should be less\n or equal than \'End Year\'!');
        years_error.html(years_error.html().replace(/\n/g, '<br/>'));

        $('#years_form_group').addClass('has-error');
    }
});