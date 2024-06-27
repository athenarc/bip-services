$(document).ready(function()
{
   //first_call($("#graphviz_test"));
   first_call(document.getElementById("graphviz_test"));
   
   //Run Ajax
   pmc = window.location.href;
   pmc = pmc.split("PMC")[1];
   pmc = pmc.replace("#", '');
   
   //Required for post requests in yii
   var csrfToken = $('meta[name="csrf-token"]').attr("content");
   //alert("Request tp: " + window.location.origin + '/bip/web/index.php/site/getgraph');
   $.ajax(
    {
        url:   window.location.origin + '/bip/web/index.php/site/getgraph',
        type: 'POST',
        data: 
        {
            'pmc' : pmc,
            _csrf : csrfToken
        },
        success: function(data) 
        {
              //Remove loading
              $('#loading_graph_div').hide();
              $('#graphviz_test').show();
              //Function that renders graph result
              response_data = data;
              //alert(data);
              $('#radio_refresh_buttons').css('visibility', 'hidden');
              handle_response(response_data);
        },
        error: function(e) 
        {
            alert("There was an error getting graph data!");
        }
    });
});
