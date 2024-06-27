$(document).ready(function()
{
	$('#remove_filters').click( (e) => {
		e.preventDefault();
		$('#clear_all_input').val(1);

		// clear year inputs, else form cannot be submitted  
		$('#start_year_input').val('');
		$('#end_year_input').val('');
		$('#years_error').hide();
		$('#years_form_group').removeClass('has-error');
		
		$('#search-form').submit();
		return false;
	});
});