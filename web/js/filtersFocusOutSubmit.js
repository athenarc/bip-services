$(document).ready(function()
{
	$('#start_year_input, #end_year_input').on('focusout', () => {
    	$("#search-form").trigger('submit');
	});
});