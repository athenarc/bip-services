

function getPDFLink(url, doi) {

   var csrfToken = $('meta[name="csrf-token"]').attr("content");

   $.ajax({
        url:   url,
        type: 'GET',
        data: 
        {
            doi : doi,
            _csrf : csrfToken
        },
        success: function(pdf_link) 
        {
            if (pdf_link) {
            	$('#pdf_button').attr("href", pdf_link)
            	$('#pdf_button').removeClass('disabled');
            }
        },
        error: function(e) 
        {
            console.error("There was an error getting pdf link for: " + doi);
        }
    });
}

$(document).ready(function()
{
	// get pdf link when document is loading
	$('#pdf_button').click();
});