var createCookie = function(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

function getCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

/**
 * It deletes the contents of the cookie (and make it expire).
 */
var delete_cookie = function(name) {
	var date = new Date();
    date.setTime(date.getTime());
    expires = "; expires=" + date.toGMTString();
    document.cookie = name + "=" + expires + "; path=/";
};

/**
 * It updates the message in 'comparison' div (this div displays how many articles are
 * selected for comparison.
 *
 * @author Thanasis Vergoulis
 */
function updateComparisonMsg(mode){
		var articles_to_compare;
		var articles;
		var articles_num;
		articles_to_compare = getCookie("bipComparison");		
		articles = articles_to_compare.split(",");

		if (document.getElementById("comparison")) {
			document.getElementById("comparison").style.visibility='visible'; //make sure it is visible
		}
		
		articles_num = (articles.length-1);
		if( articles_num!=0 || (articles_num==0 && mode!="init") )
		{
			//Modify properly the comparison message.
			if( articles_num==1 )
			{
				document.getElementById("comparison").innerHTML = articles_num+" article selected (select more to compare)";
				document.getElementById("comparison").className +=" disabled"; //not clickable
				document.getElementById("clear-comparison").style.visibility='visible'; //show clear all button
			}
			else if( articles_num==0 )
			{
				document.getElementById("comparison").innerHTML = "No articles selected";			
				document.getElementById("comparison").className +=" disabled"; //not clickable
				document.getElementById("clear-comparison").style.visibility='hidden'; //hide clear all button
			}
			else if( articles_num>4 )
			{
				document.getElementById("comparison").innerHTML = articles_num+" articles selected (MAX 4 can be compared! - deselect "+(articles_num-4)+" to proceed)";
				document.getElementById("comparison").className +=" disabled"; //not clickable
				document.getElementById("clear-comparison").style.visibility='visible'; //show clear all button
			}
			else
			{
				document.getElementById("comparison").innerHTML = articles_num+" articles (click to compare)";
				document.getElementById("clear-comparison").style.visibility='visible'; //show clear all button
				var pos = document.getElementById("comparison").className.search("disabled");
				if( pos!=-1 )
					document.getElementById("comparison").className = document.getElementById("comparison").className.substring(0,pos);
			}

			if( articles_num==0 )
				setTimeout(function(){document.getElementById("comparison").style.visibility='hidden';},3000); //message to disappear after 3 sec
		}
		else //if init mode and 0 for comparison
		{
			if (document.getElementById("comparison")) {
				document.getElementById("comparison").style.visibility='hidden';
			}
		}
		return;
}

/**
 * It highlights the given row.
 *
 * @author Thanasis Vergoulis
 */
function highlightRow(row_id){
	$(`#${row_id}`).removeClass("panel-default");
	$(`#${row_id}`).addClass("panel-success");
}

/**
 * It clears the highlight of the given row. 
 */
function clearRow(row_id){
	$(`#${row_id}`).removeClass("panel-success");
	$(`#${row_id}`).addClass("panel-default");
}

/**
 * If the given row is highlighted, then return true. Otherwise, return false.
 */
function isHighlighted(row_id){
	return $(`#${row_id}`).hasClass("panel-success");
}

/**
 * It highlights any entry that is selected in the current page. 
 *
 * @author Thanasis Vergoulis
 */
function highlightSelectedRows(){
	//Get the ids of selected (see in the cookie)
	var cookie_contents;
	var selected_ids;
	var cookie_name; cookie_name = "bipComparison";
	cookie_contents = getCookie(cookie_name);
	selected_ids = cookie_contents.split(",");
	$.each(selected_ids, function(index, value){
		if( value!="")
		{
			var cur_id;
			cur_id = "res_"+value;
			if( document.getElementById(cur_id)!== null)
				highlightRow(cur_id);
		}
	});
}

/**
 * It removes all selected entries from the cookie and make it to expire. Moreover, it
 * updates the comparison message. 
 *
 * @author Thanasis Vergoulis
 */
function clearSelected(){
	//First remove the highlight from rows...
	var cookie_contents;
	var selected_ids;
	var cookie_name; cookie_name = "bipComparison";
	cookie_contents = getCookie(cookie_name);
	selected_ids = cookie_contents.split(",");
	$.each(selected_ids, function(index, value){
		if( value!="")
		{
			var cur_id;
			cur_id = "res_"+value;
			if( document.getElementById(cur_id)!== null)
				clearRow(cur_id);
		}
	});

	//Clear the cookie
	delete_cookie("bipComparison"); 
	
	//Update the message.
	updateComparisonMsg("regular");
}

/**
 * It is used after the user clicks on the remove button that exists in every row in the
 * comparison page. It just removes the selected paper from the comparison list. 
 * 
 * @author Thanasis Vergoulis
 */
function clickRemoveBtn(paper_id){
		var value;
		var name = "bipComparison";
		value = getCookie(name);
		var days; days = 10; 
		value = value.replace(paper_id+",","");
		
		createCookie(name,value,days); //update cookie
		updateComparisonMsg("regular"); //update message
}

$(document).ready(function(){
	updateComparisonMsg("init"); //run for the first time...
	highlightSelectedRows();
});

/**
 * This block of code is executed each time the user clicks on a result item. 
 *
 * @author Thanasis Vergoulis
 */
$(".selected-after-click").click(function(event){

	if (!event.target.id) 
		return;

	var tokens = event.target.id.split("_");
	var row_id = tokens[0]+"_"+tokens[1];
	var clicked_internal_article_id = tokens[1];

	if( !isHighlighted(row_id) ) //If not highlighted then highlight!
	{
		highlightRow(row_id); 
		
		var expires;
		var days; days=10;
		var name; name="bipComparison";
		var value;
		
		value = getCookie(name);				
		value += clicked_internal_article_id+",";
				
 		createCookie(name,value,days); //update cookie 		
 		updateComparisonMsg("regular"); //update message		
	}
	else //undo
	{
		clearRow(row_id);
		var value;
		var name = "bipComparison";
		value = getCookie(name);
		var days; days = 10; 
		value = value.replace(clicked_internal_article_id+",","");
		
		createCookie(name,value,days); //update cookie
		updateComparisonMsg("regular"); //update message
	}
	
});


