
$(document).ready(function(){
	highlightSelectedRows();
});

// highlight rows that have checked checkboxes
function highlightSelectedRows(){
	let selected_papers_ids = getSelectedCheckboxIds();
	selected_papers_ids.forEach( (paper_id) => {
		let row_id = `res_${paper_id}`;
		highlightRow(row_id);
	});
}

// highlights or clears a row based on the corresponding checkbox
function handleCheckboxClick(checkbox){
	let row_id = `res_${checkbox.value}`;
	(checkbox.checked) ? highlightRow(row_id) : clearRow(row_id);
}

/**
 * It highlights the given row.
 *
 * @author Thanasis Vergoulis
 */
function highlightRow(row_id){
	document.getElementById(row_id).className += " success";
}

/**
 * It clears the highlight of the given row. 
 */
function clearRow(row_id){
	var pos = document.getElementById(row_id).className.search("success");
	document.getElementById(row_id).className = document.getElementById(row_id).className.substring(0,pos);
}

/**
 * If the given row is highlighted, then return true. Otherwise, return false.
 */
function isHighlighted(row_id){
	var pos = document.getElementById(row_id).className.search("success");
	if( pos==-1 )
		return false;
	else
		return true;
}

// removes results and comments form and show loading
function showLoading(){
	$("#results_set").hide();
	$("#results_hdr").hide();
	$("#loading_results").show();
	$('form#survey-form').hide();
}

// disables search keywords input
function disableKeywordsInput(){
	$("#keywords").prop('disabled', true);
}

// gets the ids of the selected checkboxes
function getSelectedCheckboxIds(){
	return $.map($('input[name="selected_papers"]:checked'), (val, i) => {
		return val.value;
	});
}

// 'survey-form' beforeSubmit hook that gets selected checkboxes 
// and sets hidden param 'surveyform-checked' appropriately
$('body').on('beforeSubmit', 'form#survey-form', function () {
    var form = $(this);

	// return false if form still have some validation errors
	if (form.find('.has-error').length) {
	  return false;
	}

	// get checked checkboxes 
	let selected_papers = getSelectedCheckboxIds();

	// fill hidden input papers
	$('#surveyform-checked').val(selected_papers);

	// returning true will submit the form as normal
	return true;
});