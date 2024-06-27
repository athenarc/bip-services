/*
 * Function that processes the string returned from graphviz/java program
 */
var delete_response = "graph.removeCells(graph.getChildVertices(graph.getDefaultParent()));"; //Deletes all the nodes on the camvas
var node_array = [];
var edge_array = [];

function handle_response(response) { //The function that deletes the previous graph and shows the new one
    if (response.indexOf("DIMENSION") != -1) {
        var dim = response.split("DIMENSION");
        eval(dim[0]);
        response = dim[1];
    }
    var parent = graph.getDefaultParent();
    graph.getModel().beginUpdate();
    eval(delete_response);
    eval(response);
	var dim_x=document.getElementById("graphviz_sub_parent").offsetWidth;
	var dim_y=document.getElementById("graphviz_sub_parent").offsetHeight;
	var cell_div_dimensions = graph.insertVertex(parent, 'div_dimen', '  ', dim_x, dim_y, 0, 0);
    var width = 120;
    var height = 60;
    var middle_node = 110;
    var style = graph.getStylesheet().getDefaultVertexStyle();
    style[mxConstants.STYLE_FONTSIZE] = 11;
    for (i = 0; i < node_array.length; i++) {
        if (node_array[i][4] == 1) {
            var id = node_array[i][0];
            var label = node_array[i][1];
            var x = node_array[i][2];
            var y = node_array[i][3];
            //graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=http://83.212.97.26:8080/medline/Document-48-green.png;');
            graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=' + window.location.origin + '/bip/web/img/Document-48-green.png;');
            //graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=http://localhostbip/web/img/Document-48-green.png;');
        } else {
            if (node_array[i][4] == 2) {
                var id = node_array[i][0];
                var label = node_array[i][1];
                var x = node_array[i][2];
                var y = node_array[i][3];
                //graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=http://83.212.97.26:8080/medline/Document-48-grey-light.png;');
                graph.insertVertex(parent, id, label, x, y, middle_node, height, style = 'labelPosition=right;image=' + window.location.origin + '/bip/web/img/Document-48-grey-light.png;');
                //graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=http://localhost/bip/web/img/Document-48-grey-light.png;');   
            } else {
                var id = node_array[i][0];
                var label = node_array[i][1];
                var x = node_array[i][2];
                var y = node_array[i][3];
                //graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=http://83.212.97.26:8080/medline/Document-48-grey.png;');
                graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=' + window.location.origin + '/bip/web/img/Document-48-grey.png;');
                //graph.insertVertex(parent, id, label, x, y, width, height, style = 'labelPosition=right;image=http://localhost/bip/web/img/Document-48-grey.png;');           
            }
        }
    }
    for (i = 0; i < edge_array.length; i++) {
        if (edge_array[i][1] == 0) {
            var id = edge_array[i][0];
            if (graph.getModel().getCell(id) == null) {
                var label = edge_array[i][1];
                var x = edge_array[i][2];
                var y = edge_array[i][3];
                graph.insertEdge(parent, id, null, graph.getModel().getCell(x), graph.getModel().getCell(y));
            }
        } else {
            if (graph.getModel().getCell(edge_array[i][0]) == null) {
                var id = edge_array[i][0];
                var label = edge_array[i][1];
                var x = edge_array[i][2];
                var y = edge_array[i][3];
                graph.insertEdge(parent, id, label, graph.getModel().getCell(x), graph.getModel().getCell(y));
            }
        }
    }
    graph.getModel().endUpdate();
    
    citing_requested = $('input[name=citing]:checked').val();
    cited_requested  = $('input[name=cited]:checked').val();

    citing_msg = '';
    cited_msg  = '';
    if (citing_requested != citing_found)
    {
        if (citing_found == 0)
        {
            citing_class = "bg-danger";
            citing_msg = "<p class='" + citing_class + "'>No citing papers found.</p>";
        }
        else
        {
            citing_class = "bg-warning";
            citing_msg = "<p class='" + citing_class + "'>Only " + citing_found + " citing papers found.</p>";
        }

    }

    if (cited_requested != cited_found)
    {
        if (cited_found == 0)
        {
            cited_class = "bg-danger";
            cited_msg = "<p class='" + cited_class + "'>No cited papers found.</p>";
        }
        else
        {
            cited_class = "bg-warning";
            cited_msg = "<p class='" + cited_class + "'>Only " + cited_found + " cited papers found.</p>";
        }
        
    }

    $("#graphviz_info_citing").html(citing_msg);
    $("#graphviz_info_cited").html(cited_msg);
    
    $("#graphviz_sub_parent").scrollTo(this_is_x - ($("#graphviz_parent").width() / 2) + $("#graphviz_options").width(), 0, {axis: 'x'});
    $("#graphviz_sub_parent").scrollTo(this_is_y - ($("#graphviz_parent").height() / 2), 0, {axis: 'y'});
	
}
