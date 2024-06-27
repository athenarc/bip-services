/* 
 * Ajax Call for results on comparison page
 */
$(document).ready(function()
{

    //Get top paper ids
    
   //Required for post requests in yii
   var csrfToken = $('meta[name="csrf-token"]').attr("content");
   //Get the ids to be sent to the action
   var paper_ids = [];
   var results_set = $('table.table tbody tr:not(.kwd-context)').each(function(){ paper_ids.push(this.id.replace("res_", "")); });

   $.ajax(
    {
        url:   window.location.origin + '/bip/web/index.php/site/get-comparison-graph',
        type: 'GET',
        data: 
        {
            paper_ids : paper_ids.toString(),
            _csrf : csrfToken
        },
        success: function(data) 
        {
              //Function that renders graph result
              response_data = data;
              alert("Response: \n" + data);
              //handle_response(response_data);
        },
        error: function(e) 
        {
            alert("There was an error getting graph data!");
        }
    });
    
});

