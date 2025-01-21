/*
 * Set function on radiobutton change. To be used on details view
 */
$(document).ready(function()
{
    $('#graphviz_options_radiobuttons').find('input').change(function () 
    {
        // The one that fires the event is always the
        // checked one; you don't need to test for this
        //alert($(this).val());
        
        //Empty graph area
        //$('#graphviz_test').empty().hide();
        $('#graphviz_test').hide();
        $('#loading_graph_div').show();
        
        //Run Ajax
         pmc = window.location.href;
         pmc = pmc.split("PMC")[1];
         pmc = pmc.replace("#", '');
         
        for (var i = 0; i < 10; i++)
        {
            zoom_out();
        }
        
        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");  
        //Get citing/cited number requested.
        var citing    = $('input[name=citing]:checked').val();
        var cited     = $('input[name=cited]:checked').val();
        var metric    = $('input[name=metric]:checked').val();
        var layout    = $('input[name=layout]:checked').val();
         
        $.ajax(
         {
             url:   `${appBaseUrl}/site/getgraph`,
             type: 'POST',
             data: 
             {
                 'pmc' : pmc,
                 _csrf : csrfToken,
                 citing: citing,
                 cited:  cited,
                 metric: metric,
                 layout: layout,
             },
             success: function(data) 
             {
                   //Remove loading
                   $('#loading_graph_div').hide();
                   $('#graphviz_test').show();
                   //Function that renders graph result
                   response_data = data;
                   $('#radio_buttons_refresh').css('visibility', 'hidden');
                   handle_response(response_data);
             },
             error: function(e) 
             {
                 alert("There was an error getting graph data!");
             }
         });        
    });  
    
    //Add a clickhandler on the refresh button
    $('#radio_buttons_refresh button').on('click', function () 
    {
        for (var i = 0; i < 10; i++)
        {
            zoom_out();
        }
        $("#radio_buttons_refresh").css('visibility', 'hidden');        
        handle_response(response_data);
    });   
    
    $('#radio_buttons_restore button').on('click', function () 
    {
        $('#graphviz_test').hide();
        $('#loading_graph_div').show();
        
        //Run Ajax
         pmc = window.location.href;
         pmc = pmc.split("PMC")[1];
         pmc = pmc.replace("#", '');
         

        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");  
        //Get citing/cited number requested.
        var citing    = 5;
        var cited     = 5;
        var metric    = 'popularity';
        var layout    = 1;
        
        $('input:radio[name="citing"]').filter('[value="5"]').prop('checked', true);
        $('input:radio[name="cited"]').filter('[value="5"]').prop('checked', true);
        $('input:radio[name="metric"]').filter('[value="popularity"]').prop('checked', true);
        $('input:radio[name="layout"]').filter('[value="1"]').prop('checked', true);
        
        /* fully zoom in */
        for (var i = 0; i < 10; i++)
        {
            zoom_out();
        }    
         
        $.ajax(
         {
             url:   `${appBaseUrl}/site/getgraph`,
             type: 'POST',
             data: 
             {
                 'pmc' : pmc,
                 _csrf : csrfToken,
                 citing: citing,
                 cited:  cited,
                 metric: metric,
                 layout: layout,
             },
             success: function(data) 
             {
                   //Remove loading
                   $('#loading_graph_div').hide();
                   $('#graphviz_test').show();
                   //Function that renders graph result
                   response_data = data;
                   $('#radio_buttons_refresh').css('visibility', 'hidden');
                   handle_response(response_data);
             },
             error: function(e) 
             {
                 alert("There was an error getting graph data!");
             }
         });     
    });
});