function cookieAcceptClick(link)
{

	var button=document.getElementsByClassName("cookie-container")[0];

	button.style.display="none";

	$.ajax({
    		url: link,
    		context: document.body,
    		success: function(){ return 0;}
			});


}
