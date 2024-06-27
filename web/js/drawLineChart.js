/* 
 * Functions that draw a Citation Count history line  chart to compare papers.
 */
function drawLineChart(min_x, max_x, max_y, data)
{    
    //------------------------------------------------------------------------//
    //Create canvas & viewports
    
    // Set the dimensions of the canvas / graph
    var margin = {top: 50, right: 60, bottom: 50, left: 60},
    width = 800 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;
    //The svg element on which we append our graphics
    var svg = d3.select(".citation-line-chart").append("svg")
    .attr("width", width + margin.left + margin.right).attr("height", height + margin.top + margin.bottom);
    // responsive(?)
    // .attr("viewBox", `0 0 ${width + margin.left + margin.right} ${height + margin.top + margin.bottom}`);

    //graphics group
    var g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top +")");
    //------------------------------------------------------------------------//
            
    //--------------------------------------------------------------------//
    //Set range, domain & axes
        
    //Since x-axis corresponds to years, we need to set the domain with dates and later scale it accordingly
    var x = d3.scaleLinear().domain([min_x,max_x]).range([0, width]);
    //y axis is Citation Count. This can be scaled linearly
    var y = d3.scaleLinear().domain([0, max_y]).range([height-margin.top, margin.bottom]);
            
    // Define the axes
    var xAxis = d3.axisBottom().scale(x).tickFormat(d3.format("d")).ticks((max_x-min_x));
    var yAxis = d3.axisLeft().scale(y).ticks(5);
        
    //Add axes to chart
    svg.append("g").attr("class", "xaxis")
                   .attr("transform", "translate(" + margin.left + "," + (height-margin.bottom) + ")")
                   .call(xAxis)
                   .selectAll("text")	
                   .style("text-anchor", "end")
                   .attr("dx", "-.8em")
                   .attr("dy", ".15em")
                   .attr("transform", function(d) 
                    {
                        return "rotate(-55)" 
                    });
              
    svg.append("g").attr("class", "y axis")
                   .attr("transform", "translate(" + margin.left + ",0)")
                   .call(yAxis);
           
    //--------------------------------------------------------------------//
    //Add axis titles
    svg.append("text")
       .attr("text-anchor", "middle")  // this makes it easy to centre the text as the transform is applied to the anchor
       .attr("transform", "translate(" + margin.left/5 + "," +(height/2) +")rotate(-90)")  // text is drawn off the screen top left, move down and out and rotate
       .text("Num. Citations");
               
    svg.append("text")
       .attr("text-anchor", "middle")  // this makes it easy to centre the text as the transform is applied to the anchor
       .attr("transform", "translate("+ (width/2) +","+(height+margin.bottom/2)+")")  // centre below axis
       .text("Year");
        
    //--------------------------------------------------------------------//
    
    //--------------------------------------------------------------------// 
    //Line creation function
    var lineFunc = d3.line()
                    .x(function(d) 
                    {
                        return x(d.year);
                    })
                    .y(function(d) 
                    {
                        return y(d.cc);
                    })
                    .curve(d3.curveLinear)
   
    //------------------------------------------------------------------------//
    // set the colour scale
    used_colors = [];
    //Get the colors for each paper - use the id as key
    //This must be done, because the order of elements looped by js/php 
    //is not the same in associative arrays. Thus we may correctly present
    //the curves, but with a wrong correspondence of colors, if we do not
    //rearrange them
    $('i.fa.fa-circle').each(function()
    {
      item =  $(this).parent().parent().attr('id');
      item = item.replace("res_", "");
      color = $(this).css('color');
      used_colors[item]= color;
    });
    //simply colors, without keys
    colors = [];
    for (item in used_colors)
    {
        colors.push(used_colors[item]);
    }
    
    if (colors.length > 0)
    {
        var color = d3.scaleOrdinal().range(colors);
    } 
    else
    {
        var color = d3.scaleOrdinal().range(['#4682B4', '#FF0000', '#F4D03F']);
    }
    
    //------------------------------------------------------------------------//
         
    //------------------------------------------------------------------------//
    //Draw the actual data. 
    var focusCircleArray = [];
    var focusCircleTextArray = [];
    //Get the data and append to svg - do this for each item in the data
    //Begin with areas and continue with lines
    for (var item in data)
    {
        //alert(JSON.stringify(data[item], null, 2));
        //Add a focus circle for current element
        svgCircleGroupElement  = svg.append("g");
        focusCircleArray[item] = svgCircleGroupElement
                                    .append("circle")  
                                    //.attr("class", item)                       
                                    .attr("class", "y " + item) 
                                    .attr("r", 4)
                                    .style("display", "none")
                                    .style("fill", "none")                      
                                    .style("stroke", function() 
                                    { // Add dynamically
                                        return data[item].color = color(item); 
                                    });
         focusCircleTextArray[item] = svgCircleGroupElement.append("text")
                 .attr('class', item)
                 .style("display", "none")
                 .style("stroke", function() 
                 { // Add dynamically
                    return data[item].color = color(item); 
                 });
                                    
        
        //Line
        svg.append('path')
           .data(data[item])
           .attr("class", "line")
           .attr("d", lineFunc(data[item]))
           .attr("transform", "translate(" + margin.left + ",0)")
           .style("stroke", function() 
            { // Add dynamically
                return data[item].color = color(item); 
            })
           .style("fill", function() 
            { // Add dynamically
                return data[item].color = color(item); 
            })
            .style("fill-opacity", 0.2)
            .on('mouseover', function ()
            {
		//Bring back the hovered over blob
		d3.select(this)
		  .transition().duration(200)
		  .style("fill-opacity", 0.5);	
            })
            .on('mouseout', function()
            {
		//Bring back all blobs
		d3.selectAll('.line').style("fill-opacity", 0.2);
            })
    }
    
    //------------------------------------------------------------------------//
    //ADD functions to drawn data
    
    //Store date range
    date_array = [];
    for (var i = min_x; i <= max_x; i++) 
    {
        date_array.push(i);
    } 
    //Define date bisector
    bisect = d3.bisector(function(d) { return d; }).left;
    //Function to get mouse position over svg element. This will be used to highlight
    // - if they exist, the circles showing the data.
    svg.on('mousemove', function()
    {
        //Get mouse position, get x value of it and revert to year
        var x0 = x.invert(d3.mouse(this)[0]-margin.left), 
            //Split date array based on position of year found.
            i = bisect(date_array, x0);
            //Check distances towards either year (left or right)
            distanceLeft = x0 - date_array[i-1];
            distanceRight = date_array[i] - x0;
            
            //Get smallest distance and select that year to highlight
            selectedOffset = (distanceLeft <= distanceRight) ? i-1 : i;
            yOffsets = [];
            for(item in data)
            {
                //Remove values that were only added to close the line
                // and clone the array
                tempCopyArray = data[item].slice(1,-1);
                //Get starting year of paper history currently examined
                startYear = tempCopyArray[0].year;
                yOffsets.push(item);
                if (date_array[selectedOffset] >= startYear)
                {
                    //Get offset of year in dates array
                    offsetOfDate =  date_array[selectedOffset] - startYear+1;                    
                    //Get year and pixel offset for year, for item in question
                    year = data[item][offsetOfDate].year;
                    yearOffset = x(data[item][offsetOfDate].year) + margin.left;
                    //Get cc and cc pixel offset for item in question
                    cc = data[item][offsetOfDate].cc;
                    ccOffset = y(cc);
                    //Move the circle on the lines
                    //Probably x-axis doesn't work due to missing year data.
                    focusCircleArray[item].attr("transform", "translate(" + yearOffset + "," + ccOffset + ")")
                                          .style("display", 'inline')
                                          .style("z-index", 100);
                       
                    requiredYOffset = margin.top + (yOffsets.length * 16 );
                
                    focusCircleTextArray[item].text(cc)
                                          .attr("transform", "translate(" + (yearOffset + 5) + "," + requiredYOffset + ")")
                                          .style("display", null)
                                          .style("z-index", 200);
                                  
                    
                                
                }
                else
                {
                    //Remove circles in the areas where there are no lines
                    focusCircleArray[item].style("display", "none");
                    focusCircleTextArray[item].style("display", "none");
                }
                
            }
    })
    .on('mouseout', function()
    {
        //Remove circles outside graph
        for(item in data)
        {
            focusCircleArray[item].style("display", "none");
            focusCircleTextArray[item].style("display", "none");
            
        }
    });
    
  //-----------------------------------------------------------------------//
  //Set Chart title
  
//   svg.append("text")
//         .attr("x", (width / 2))             
//         .attr("y", margin.top/2)
//         .attr("text-anchor", "middle")  
//         .style("font-size", "16px") 
//         .style("text-decoration", "underline")  
//         .text("Open-Access Citations Received Per Year");
    
  //-----------------------------------------------------------------------//
     //alert("Length is: " + color.length);     
    if(colors.length === 0)
    {
      //Set chart legend
      var legend_keys = ["Selected Paper", "Avg Exceptional Paper", "Avg Substantial Paper"];
      var lineLegend = svg.selectAll(".lineLegend").data(legend_keys)
        .enter().append("g")
        .attr("class","lineLegend")
        .attr("transform", function (d,i) 
            {
                //"translate(0," + (i*20)+")";
                "translate("  + (100+100*i) + "," + height + ")";
            });

    lineLegend.append("text").text(function (d) {return d;})
        .attr("transform", function(d,i) 
        {
            return "translate(" + (1.5*margin.left + 200*i) + "," + (height + margin.top + margin.bottom/4) + ")";
        });

    lineLegend.append("circle")
        .attr("fill", function (d, i) {return color(d); })
        .attr("r", 5)
        .attr("width", 20).attr("height", 20)
        /*.style("stroke", function() 
        { // Add dynamically
             return data[item].color = color(item); 
        })*/
        .attr("transform", function(d,i) 
        {
            return "translate(" + (1.5*margin.left + 200*i-20) + "," + (height + margin.top + margin.bottom/8) + ")";
        });
    }
}
