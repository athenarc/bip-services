/*
 * Initialise xgraph library, which is needed to display graph
 */
let graph; // mxgraph variable
let mouse_down = 0;
let x_start;
let y_start;
let cursorX;
let cursorY;
let cursorX2;
let cursorY2;
const sth = 0;
let dimen_x = 0, dimen_y = 0;
// Initialise a response data variable
let response_data;

function first_call(container) {
    // Creates the graph inside the given container
    graph = new mxGraph(container);
    graph.setHtmlLabels(true);
    mxConstants.VERTEX_SELECTION_COLOR = 'none';
    mxConstants.EDGE_SELECTION_COLOR = 'none';
    mxConstants.LOCKED_HANDLE_FILLCOLOR = 'none';
    mxConstants.HANDLE_SIZE = 0;
    mxConstants.LABEL_HANDLE_SIZE = 0;
    mxConstants.RENDERING_HINT_FASTEST = true;
    mxConstants.CURSOR_MOVABLE_EDGE = 'default';
    mxConstants.CURSOR_MOVABLE_VERTEX = 'pointer';

    // Changes the default vertex style in-place
    let style = graph.getStylesheet().getDefaultVertexStyle();
    style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_IMAGE;
    style[mxConstants.STYLE_CLONABLE] = 0;
    style[mxConstants.STYLE_EDITABLE] = 0;
    style[mxConstants.STYLE_RESIZABLE] = 0;
    style[mxConstants.STYLE_STROKEWIDTH] = 1;
    style[mxConstants.STYLE_FONTCOLOR] = '#888888';
    style[mxConstants.STYLE_GRADIENTCOLOR] = '#FbFBFB';
    style[mxConstants.STYLE_FONTFAMILY] = 'Verdana';

    style = graph.getStylesheet().getDefaultEdgeStyle();
    style[mxConstants.STYLE_STROKECOLOR] = '#888888';
    style[mxConstants.STYLE_FONTCOLOR] = '#888888';
    style[mxConstants.STYLE_ENDARROW] = mxConstants.ARROW_CLASSIC;
    style[mxConstants.STYLE_EDITABLE] = 0;
    style[mxConstants.STYLE_RESIZABLE] = 0;
    style[mxConstants.STYLE_ROUNDED] = true;
    style[mxConstants.STYLE_FONTFAMILY] = 'Verdana';
    graph.setCellsDisconnectable(false);
    // graph.setPanning(true); // We implement our own panning function. We disable the default
    // graph.panningEnabled(true);
    // console.log(graph.isPanningEnabled);
    // Default node to be used as root
    const parent = graph.getDefaultParent();
    // Automatically handle parallel edges
    const layout = new mxParallelEdgeLayout(graph);
    const layoutMgr = new mxLayoutManager(graph);
    layoutMgr.getLayout = function (cell) {
        if (cell.getChildCount() > 0) {
            return layout;
        }
    };
    // The first nodes of the graph always created- but never displayed
    const cell = graph.insertVertex(parent, 'root', '', '', 80, 60, 0, 0);
    graph.ordered = false; // Nodes are placed on top of edges
    // We initialize the listener to catch the mouse click on vertex
    graph.addListener(mxEvent.CLICK, (sender, evt) => {
        const cell = evt.getProperty('cell');
        if (cell instanceof mxCell && !(cell).isEdge()) {
            if (id == null) {
                return;
            }
            $('#dialog').dialog('open');
            // Ajax call to fill dialog contents!
            $.ajax({
                url: `${appBaseUrl}/site/papersummary?paper_id=${id}`,
                error: function () {
                    $('#dialog').html('<p>An error has occurred - please reload details!</p>');
                },
                // dataType: 'jsonp',
                success: function (data) {
                    $('#dialog').empty();
                    $('#dialog').css({ 'text-align': 'left' });
                    $('#dialog').append(data);
                    id = null;
                },
                type: 'GET',
            });
        } // instance of cell
    });
    graph.addMouseListener({
        cell: null,
        mouseDown: function (sender, e) {
            // console.log("mouse down "+node_movement+" "+mouse_down);
            const hoveredCell = e.getCell();
            if ((hoveredCell) && (!(hoveredCell).isEdge())) {
                id = hoveredCell.getId();
                mouse_down = 0;
                if ($('#radio_buttons_refresh').css('visibility') === 'hidden') { $('#radio_buttons_refresh').css('visibility', 'visible'); }
            } else {
                mouse_down = 1;
                document.body.style.cursor = 'pointer';
                cursorX = e.getX(); // Get mouse position
                cursorY = e.getY();
                x_start = $('#graphviz_sub_parent').scrollLeft(); // Get window position
                y_start = $('#graphviz_sub_parent').scrollTop();
            }
        },
        mouseMove: function (sender, e) {},
        mouseUp: function (sender, e) {},
    });
}

function zoom(number) {
    graph.getView().setScale(number);
    const parent = graph.getDefaultParent();
    graph.getModel().beginUpdate();
    if (dimen_x == 0) { dimen_x = document.getElementById('graphviz_sub_parent').offsetWidth; }
    if (dimen_y == 0) { dimen_y = document.getElementById('graphviz_sub_parent').offsetHeight; }
    const dim_x_ = dimen_x / number;
    const dim_y_ = dimen_y / number;
    const cell = graph.getModel().getCell('div_dimen');
    graph.model.remove(cell);
    graph.insertVertex(parent, 'div_dimen', ' ', dim_x_, dim_y_, 0, 0);
    graph.getModel().endUpdate();
    if ($('#radio_buttons_refresh').css('visibility') === 'hidden') { $('#radio_buttons_refresh').css('visibility', 'visible'); }
}

let delta = 0;
let zoom_factor = 10;
const zoom_array = new Array();
zoom_array[1] = 0.1;
zoom_array[2] = 0.2;
zoom_array[3] = 0.3;
zoom_array[4] = 0.4;
zoom_array[5] = 0.5;
zoom_array[6] = 0.6;
zoom_array[7] = 0.7;
zoom_array[8] = 0.8;
zoom_array[9] = 0.9;
zoom_array[10] = 1;

function zoom_in() {
    if (zoom_factor > 1) {
        zoom_factor--;
        zoom(zoom_array[zoom_factor]);
    }
}

function zoom_out() {
    if (zoom_factor < 10) {
        zoom_factor++;
        zoom(zoom_array[zoom_factor]);
    }
}


/** This is high-level function.
             * It must react to delta being more/less than zero.
             */
function handle(delta) {
    if (delta < 0) {
        zoom_in();
    } else {
        zoom_out();
    }
}
/** Event handler for mouse wheel event.
             */
function wheel(event) {
    delta = 0;
    if (!event) /* For IE. */ { event = window.event; }
    if (event.wheelDelta) {
        /* IE/Opera. */
        delta = event.wheelDelta / 120;
    } else
        if (event.detail) {
            /** Mozilla case. */
            /** In Mozilla, sign of delta is different than in IE.
                 * Also, delta is multiple of 3.
                 */
            delta = -event.detail / 3;
        }
    /** If delta is nonzero, handle it.
             * Basically, delta is now positive if wheel was scrolled up,
             * and negative, if wheel was scrolled down.
             */

    if (delta) {
        if ($('#radio_buttons_refresh').css('visibility') === 'hidden') { $('#radio_buttons_refresh').css('visibility', 'visible'); }
        handle(delta);
    }
    /** Prevent default actions caused by mouse wheel.
             * That might be ugly, but we handle scrolls somehow
             * anyway, so don't bother here..
             */
    if (event.preventDefault) { event.preventDefault(); }
    event.returnValue = false;
}

jQuery(document).ready(() => // Show hide labels
{
    if (window.addEventListener) { /** DOMMouseScroll is for mozilla. */
        document.getElementById('graphviz_test').addEventListener('DOMMouseScroll', wheel, false);
        document.getElementById('graphviz_test').addEventListener('mousewheel ', wheel, false); // Chrome
    }
    /** IE/Opera. */
    document.getElementById('graphviz_test').onmousewheel = wheel;
});

jQuery(document).ready(() => // Show hide labels
{
    document.onmouseup = function (e) {
        mouse_down = 0;
        // Mouse is up
        document.body.style.cursor = 'auto';
    };
    document.onmousemove = function (e) {
        if (mouse_down == 1) // If mouse is down
        {
            document.body.style.cursor = 'pointer';
            cursorX2 = e.clientX;
            cursorY2 = e.clientY;

            const x_cur = x_start + cursorX - cursorX2;
            const y_cur = y_start + cursorY - cursorY2;
            $('#graphviz_sub_parent').scrollLeft(x_cur);
            $('#graphviz_sub_parent').scrollTop(y_cur);
            /*
            document.body.style.cursor = "pointer";
            cursorX2 = e.pageX;
            cursorY2 = e.pageY;
            var x_cur = x_start + cursorX - cursorX2;
            var y_cur = y_start + cursorY - cursorY2;
            $('#graphviz_test').scrollLeft(x_cur);
            $('#graphviz_test').scrollTop(y_cur); */
        }
        e.returnValue = false;
    };

    $('#graphviz_test').dblclick(e => {
        zoom_out();
    });

    document.getElementById('graphviz_test').addEventListener('contextmenu', e => {
        e.preventDefault();
        zoom_in();
        return false;
    }, false);
});

