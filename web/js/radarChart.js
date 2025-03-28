/////////////////////////////////////////////////////////
/////////////// The Radar Chart Function ////////////////
/////////////// Written by Nadieh Bremer ////////////////
////////////////// VisualCinnamon.com ///////////////////
/////////// Inspired by the code of alangrafu ///////////
/////////////////////////////////////////////////////////
	
function RadarChart(id, data, options, actual_values) {
	var cfg = {
	 w: 600,				//Width of the circle
	 h: 600,				//Height of the circle
	 margin: {top: 20, right: 20, bottom: 20, left: 20}, //The margins of the SVG
	 levels: 3,				//How many levels or inner circles should there be drawn
	 maxValue: 0, 			//What is the value that the biggest circle will represent
	 labelFactor: 1.25, 	//How much farther than the radius of the outer circle should the labels be placed
	 wrapWidth: 60, 		//The number of pixels after which a label needs to be given a new line
	 opacityArea: 0.35, 	//The opacity of the area of the blob
	 dotRadius: 4, 			//The size of the colored circles of each blog
	 opacityCircles: 0.1, 	//The opacity of the circles of each blob
	 strokeWidth: 2, 		//The width of the stroke around each blob
	 roundStrokes: false,	//If true the area and stroke will follow a round path (cardinal-closed)
	 color: d3.scaleOrdinal(d3.schemeCategory10)	//Color function
	};
	
	//Put all of the options into a variable called cfg
	if('undefined' !== typeof options){
	  for(var i in options){
		if('undefined' !== typeof options[i]){ cfg[i] = options[i]; }
	  }//for i
	}//if
	
	//If the supplied maxValue is smaller than the actual one, replace by the max in the data
	// var maxValue = Math.max(cfg.maxValue, d3.max(data, function(i){return d3.max(i.map(function(o){return o.value;}))}));
	var maxValue = 1; //
	var allAxis = (data[0].map(function(i, j){return i.axis})),	//Names of each axis
		total = allAxis.length,					//The number of different axes
		radius = Math.min(cfg.w/2, cfg.h/2), 	//Radius of the outermost circle
		Format = d3.format('%'),			 	//Percentage formatting
		angleSlice = Math.PI * 2 / total;		//The width in radians of each "slice"
	
	// Scale for the radius; for linear scale uncomment this and comment below
	// var rScale = d3.scaleLinear()
	// 	.range([0, radius])
	// 	.domain([0, maxValue]);
	
	// ranges [1,5,10,50,100]
	// var rScale = d3.scaleSequential(function(t) {
	// 	if(t < 0.01){
	// 		return (t / 0.01 * 0.2) * radius;
	// 	}else if(0.01 <= t && t < 0.05){
	// 		return ((t - 0.01) / 0.04 * 0.2 + 0.2) * radius;
	// 	}else if(0.05 <= t && t < 0.1){
	// 		return ((t - 0.05) / 0.05 * 0.2 + 0.4) * radius;
	// 	}else if(0.1 <= t && t < 0.5){
	// 		return ((t - 0.1) / 0.4 * 0.2 + 0.6) * radius;
	// 	}else if(0.5 <= t && t <= 1){
	// 		return ((t - 0.5)  / 0.5 * 0.2 + 0.8) * radius;
	// 	}
	// }).domain([0, maxValue]);

	var rScale = d3.scaleSequential(function(t) {
		if(t < 0.0001){
			return (t / 0.0001 * 0.2) * radius;
		}else if(0.0001 <= t && t < 0.001){
			return ((t - 0.0001) / (0.001 - 0.0001) * 0.2 + 0.2) * radius;
		}else if(0.001 <= t && t < 0.01){
			return ((t - 0.001) / (0.01 - 0.001) * 0.2 + 0.4) * radius;
		}else if(0.01 <= t && t < 0.1){
			return ((t - 0.01) / (0.1 - 0.01) * 0.2 + 0.6) * radius;
		}else if(0.1 <= t && t <= 1){
			return ((t - 0.1)  / (1 - 0.1) * 0.2 + 0.8) * radius;
		}
	}).domain([0, maxValue]);

	/////////////////////////////////////////////////////////
	//////////// Create the container SVG and g /////////////
	/////////////////////////////////////////////////////////

	//Remove whatever chart with the same id/class was present before
	d3.select(id).select("svg").remove();
	
	//Initiate the radar chart SVG
	var svg = d3.select(id).append("svg")
			.attr("width",  cfg.w + cfg.margin.left + cfg.margin.right)
			.attr("height", cfg.h + cfg.margin.top + cfg.margin.bottom)
			.attr("class", "radar"+id);
	//Append a g element		
	var g = svg.append("g")
			.attr("transform", "translate(" + (cfg.w/2 + cfg.margin.left) + "," + (cfg.h/2 + cfg.margin.top) + ")");
	
	/////////////////////////////////////////////////////////
	////////// Glow filter for some extra pizzazz ///////////
	/////////////////////////////////////////////////////////
	
	//Filter for the outside glow
	var filter = g.append('defs').append('filter').attr('id','glow'),
		feGaussianBlur = filter.append('feGaussianBlur').attr('stdDeviation','2.5').attr('result','coloredBlur'),
		feMerge = filter.append('feMerge'),
		feMergeNode_1 = feMerge.append('feMergeNode').attr('in','coloredBlur'),
		feMergeNode_2 = feMerge.append('feMergeNode').attr('in','SourceGraphic');

	/////////////////////////////////////////////////////////
	/////////////// Draw the Circular grid //////////////////
	/////////////////////////////////////////////////////////
	
	//Wrapper for the grid & axes
	var axisGrid = g.append("g").attr("class", "axisWrapper");
	
	//Draw the background circles
	axisGrid.selectAll(".levels")
	   .data(d3.range(1,(cfg.levels+1)).reverse())
	   .enter()
		.append("circle")
		.attr("class", "gridCircle")
		.attr("r", function(d, i){return radius/cfg.levels*d;})
		.style("fill", "#CDCDCD")
		.style("stroke", "#CDCDCD")
		.style("fill-opacity", cfg.opacityCircles)
		.style("filter" , "url(#glow)");

	// used to rename axis labels
	let axisLabelNames = {
		1: 0.01, 
		2: 0.1, 
		3: 1, 
		4: 10, 
		5: 100
	};

	//Text indicating at what % each level is
	axisGrid.selectAll(".axisLabel")
	   .data(d3.range(1,(cfg.levels+1)).reverse())
	   .enter().append("text")
	   .attr("class", "axisLabel")
	   .attr("x", 4)
	   .attr("y", function(d){return -d*radius/cfg.levels;})
	   .attr("dy", "0.4em")
	   .style("font-size", "10px")
	   .attr("fill", "#737373")
	   .text(function(d,i) { return axisLabelNames[d] + '%'; });

	/////////////////////////////////////////////////////////
	//////////////////// Draw the axes //////////////////////
	/////////////////////////////////////////////////////////
	
	//Create the straight lines radiating outward from the center
	var axis = axisGrid.selectAll(".axis")
		.data(allAxis)
		.enter()
		.append("g")
		.attr("class", "axis");
	//Append the lines
	axis.append("line")
		.attr("x1", 0)
		.attr("y1", 0)
		.attr("x2", function(d, i){ 
			return radius * Math.cos(angleSlice*i - Math.PI/2); 
		})
		.attr("y2", function(d, i){ 
			return radius * Math.sin(angleSlice*i - Math.PI/2); 
		})
		.attr("class", "line")
		.style("stroke", "white")
		.style("stroke-width", "2px");

	//Append the labels at each axis
	axis.append("text")
		.attr("class", "legend")
		.style("font-size", "11px")
		.attr("text-anchor", "middle")
		.attr("dy", "0.35em")
		.attr("x", function(d, i){ return radius * cfg.labelFactor * Math.cos(angleSlice*i - Math.PI/2); })
		.attr("y", function(d, i){ return radius * cfg.labelFactor * Math.sin(angleSlice*i - Math.PI/2); })
		.text(function(d){return d})
		.call(wrap, cfg.wrapWidth);


	/////////////////////////////////////////////////////////
	///////////// Draw the radar chart blobs ////////////////
	/////////////////////////////////////////////////////////
	
	//The radial line function
	var radarLine = d3.lineRadial().curve(d3.curveBasisClosed)
		.radius(function(d) { return rScale(d.value); })
		.angle(function(d,i) {	return i*angleSlice; });
		
	if(cfg.roundStrokes) {
		radarLine.curve(d3.curveCardinalClosed);
	}
				
	//Create a wrapper for the blobs	
	var blobWrapper = g.selectAll(".radarWrapper")
		.data(data)
		.enter().append("g")
		.attr("class", "radarWrapper");
			
	//Append the backgrounds	
	blobWrapper
		.append("path")
		.attr("class", "radarArea")
		.attr("d", function(d,i) { return radarLine(d); })
		.style("fill", function(d,i) { return cfg.color(i); })
		.style("fill-opacity", cfg.opacityArea)
		.on('mouseover', function (d,i){
			//Dim all blobs
			d3.selectAll(".radarArea")
				.transition().duration(200)
				.style("fill-opacity", 0.1); 
			//Bring back the hovered over blob
			d3.select(this)
				.transition().duration(200)
				.style("fill-opacity", 0.7);	
		})
		.on('mouseout', function(){
			//Bring back all blobs
			d3.selectAll(".radarArea")
				.transition().duration(200)
				.style("fill-opacity", cfg.opacityArea);
		});
		
	//Create the outlines	
	blobWrapper.append("path")
		.attr("class", "radarStroke")
		.attr("d", function(d,i) { return radarLine(d); })
		.style("stroke-width", cfg.strokeWidth + "px")
		.style("stroke", function(d,i) { return cfg.color(i); })
		.style("fill", "none")
		.style("filter" , "url(#glow)");		
	
	//Append the circles
	blobWrapper.selectAll(".radarCircle")
		.data((d) => d)
		.enter()
		.append("circle")
		.attr("class", "radarCircle")
		.attr("r", cfg.dotRadius)
		.attr("cx", (d,i) => rScale(d.value) * Math.cos(angleSlice * i - Math.PI / 2))
		.attr("cy", (d,i) => rScale(d.value) * Math.sin(angleSlice * i - Math.PI / 2))
		.style("fill", (d) => cfg.color(d.id))
		.style("fill-opacity", 0.8);

	/////////////////////////////////////////////////////////
	//////// Append invisible circles for tooltip ///////////
	/////////////////////////////////////////////////////////
	
	//Wrapper for the invisible circles on top
	var blobCircleWrapper = g.selectAll(".radarCircleWrapper")
		.data(data)
		.enter().append("g")
		.attr("class", "radarCircleWrapper");
		
	//Append a set of invisible circles on top for the mouseover pop-up
	blobCircleWrapper.selectAll(".radarInvisibleCircle")
		.data(function(d,i) { return d; })
		.enter().append("circle")
		.attr("class", "radarInvisibleCircle")
		.attr("r", cfg.dotRadius*1.5)
		.attr("cx", function(d,i){ return rScale(d.value) * Math.cos(angleSlice*i - Math.PI/2); })
		.attr("cy", function(d,i){ return rScale(d.value) * Math.sin(angleSlice*i - Math.PI/2); })
		.style("fill", "none")
		.style("pointer-events", "all")
		.on("mouseover", function(d,i) {
			newX =  parseFloat(d3.select(this).attr('cx')) - 15;
			newY =  parseFloat(d3.select(this).attr('cy')) - 10;

            formatted_value = '';
            if(d.axis === "Citation Count")
            {
                formatted_value =  actual_values["cc"];
            }
            else if(d.axis == "Influence")
            {
                formatted_value = actual_values["influence"];
            }
            else if(d.axis == "Popularity") {
            	formatted_value = actual_values["popularity"];
            }
            else if(d.axis == "Impulse")
            {
				formatted_value = actual_values["impulse"];

				// show 2 decimal places
            	// formatted_value = parseFloat(Math.round(d.value * 100) / 100).toFixed(2);
            }
            else formatted_value = d.value.toFixed(20).match(/^-?\d*\.?0*\d{0,3}/)[0];
                                        
			tooltip
				.attr('x', newX)
				.attr('y', newY)
                //This sets the tooltip for each circle
				//.text(Format(d.value))
                //Mod by Ilias to show actual score up to 3 non-zero decimals
                .text(formatted_value)
				.transition().duration(200)
				.style('opacity', 1);
                        
                    /*for (var propertyName in d)   
                    {
                        alert("d[" + propertyName + "]: " + d[propertyName]);
                    }*/
		})
		.on("mouseout", function(){
			tooltip.transition().duration(200)
				.style("opacity", 0);
		});
		
	//Set up the small tooltip for when you hover over a circle
	var tooltip = g.append("text")
		.attr("class", "tooltip")
		.style("opacity", 0);
	
	/////////////////////////////////////////////////////////
	/////////////////// Helper Function /////////////////////
	/////////////////////////////////////////////////////////

	//Taken from http://bl.ocks.org/mbostock/7555321
	//Wraps SVG text	
	function wrap(text, width) {
	  text.each(function() {
		var text = d3.select(this),
			words = text.text().split(/\s+/).reverse(),
			word,
			line = [],
			lineNumber = 0,
			lineHeight = 1.4, // ems
			y = text.attr("y"),
			x = text.attr("x"),
			dy = parseFloat(text.attr("dy")),
			tspan = text.text(null).append("tspan").attr("x", x).attr("y", y).attr("dy", dy + "em");
			
		while (word = words.pop()) {
		  line.push(word);
		  tspan.text(line.join(" "));
		  if (tspan.node().getComputedTextLength() > width) {
			line.pop();
			tspan.text(line.join(" "));
			line = [word];
			tspan = text.append("tspan").attr("x", x).attr("y", y).attr("dy", ++lineNumber * lineHeight + dy + "em").text(word);
		  }
		}
	  });
	}//wrap	
	
}//RadarChart


function initRadarChart(used_colors,article_data,max_value,axis_types,axis_types_colors,axis_types_tooltips,axis_descriptions, actual_values){
			////////////////////////////////////////////////////////////// 
			//////////////////////// Set-Up ////////////////////////////// 
			////////////////////////////////////////////////////////////// 

			var margin = {top: 100, right: 100, bottom: 100, left: 100},
			width = Math.min(600, window.innerWidth - 10) - margin.left - margin.right,
			height = Math.min(width, window.innerHeight - margin.top - margin.bottom - 20);
					
			////////////////////////////////////////////////////////////// 
			////////////////////////// Data ////////////////////////////// 
			////////////////////////////////////////////////////////////// 


			////////////////////////////////////////////////////////////// 
			//////////////////// Draw the Chart ////////////////////////// 
			////////////////////////////////////////////////////////////// 
			var color = d3.scaleOrdinal()
				.range(used_colors);
                                
			var radarChartOptions = {
			  w: width,
			  h: height,
			  margin: margin,
			  maxValue: max_value,
			  levels: 5,
			  roundStrokes: true,
			  color: color,                      
			};

			//Call function to draw the Radar chart
			RadarChart(".radarChart", article_data, radarChartOptions, actual_values);

			if( axis_types.length !=0 ){			
				var x = document.getElementsByClassName("legend");
				var i;
				for (i = 0; i < x.length; i++) {
					var rect = x[i].getBoundingClientRect();
					var iDiv = document.createElement('div');
					iDiv.id = 'block_'+i;
					iDiv.style.width='24px';
					iDiv.style.height=(rect.bottom-rect.top)+'px'; 
					iDiv.style.position = 'absolute';
					iDiv.style.top = rect.top+"px";
					iDiv.style.left = (rect.right+1)+"px";
					iDiv.style.zIndex = '1000';
					iDiv.style.cursor = 'pointer';
					iDiv.innerHTML = '<i class="fa fa-question-circle-o" aria-hidden="true" title="'+axis_descriptions[i]+'"></i>';
					iDiv.style.textAlign = 'left';

					document.getElementsByTagName('body')[0].appendChild(iDiv);

					var typeDiv = document.createElement('div');
					typeDiv.id = 'type_'+i;
					typeDiv.style.position = 'absolute';
					typeDiv.style.top = (rect.bottom)+"px";
					typeDiv.style.left = (rect.left+2)+"px";
					typeDiv.style.zIndex = '1000';
					typeDiv.innerHTML = "<span title='"+axis_types_tooltips[i]+"'>"+axis_types[i]+"</span>";
					typeDiv.style.color = axis_types_colors[i]; 
					typeDiv.style.cursor = 'pointer';
					typeDiv.style.textAlign = 'left';

					document.getElementsByTagName('body')[0].appendChild(typeDiv);
				}
			}
}