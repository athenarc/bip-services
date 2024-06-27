/* 
 * Display a pyramid showing where the paper is situated in terms of rankings
 */
function chartPyramid(containerId, percentage, rankType, journal)
{   
    switch(percentage)
    {
        case 100:
        {
            color_array = ['black', 'black','black','black','black','black'];
            break;
        }
        case 50:
        {
            color_array = ['grey', 'black','black','black','black','black'];
            break;
        }
        case 25:
        {
            color_array = ['grey', 'grey','black','black','black','black'];
            break;
        }
        case 10:
        {
            color_array = ['grey', 'grey','grey','black','black','black'];
            break;
        }
        case 5:
        {
            color_array = ['grey', 'grey','grey','grey','black','black'];
            break;
        }   
        case 1:
        {
            color_array = ['grey', 'grey','grey','grey','grey','black'];
            break;
        }
    }  
    
    if (journal !== "")
    {
        title = rankType + ' Rank in ' + journal;
    }
    else
    {
        title = rankType + ' Rank';
    }
    
    
    var json_object =
    {
        chart: 
        {
            type: 'pyramid',
            marginRight: 50,
            marginLeft: 50
        },
        title: 
        {
          text: title
          //x: -50
        },
        plotOptions: 
        {
            pyramid:  
            { 
                colors: color_array
            },
            series: 
            {
                dataLabels: 
                {
                    enabled: false,
                    format: '',//<b>{point.name}</b>',// ({point.y:,.0f})',
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                    softConnector: false
                }
            }
        },
        series: [{tooltip: {pointFormat: ''}, dataLabels: {format:''}, data: [['Bottom Half', 50],['Top 50%', 25],['Top 25%', 15],['Top 10%', 5],['Top 5%', 4],['Top 1%', 1]]}],
        legend: 
        {
          enabled: false
        },   
        exporting: { enabled: false },        
    }
    $('#' + containerId).highcharts(json_object);
};