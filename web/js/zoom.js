function zoom(number) {
     graph.getView().setScale(number); 
 };

var delta=0;
var zoom_factor = 10;
var zoom_array = new Array();
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

function zoom_in(){
	if (zoom_factor > 1) {
	zoom_factor--;
	zoom(zoom_array[zoom_factor]);
	
	}
}

function zoom_out(){
		if (zoom_factor <10) {
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
            if (!event) /* For IE. */ event = window.event;
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
                handle(delta);
            }
            /** Prevent default actions caused by mouse wheel.
             * That might be ugly, but we handle scrolls somehow
             * anyway, so don't bother here..
             */
            if (event.preventDefault) event.preventDefault();
            event.returnValue = false;
        }

	jQuery(document).ready(function() //Show hide labels
            {
				if (window.addEventListener) { /** DOMMouseScroll is for mozilla. */
					document.getElementById('graphviz_test').addEventListener('DOMMouseScroll', wheel, false);
					document.getElementById('graphviz_test').addEventListener('mousewheel ', wheel, false); //Chrome
				}
				/** IE/Opera. */
				document.getElementById('graphviz_test').onmousewheel = wheel;
			});