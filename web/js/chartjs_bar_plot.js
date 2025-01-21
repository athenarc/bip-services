function render_bar_plot(container_id, keys, values, labelText) {

    const chartColor = getComputedStyle(document.documentElement).getPropertyValue('--main-color');
    const transparentChartColor = `${chartColor}${Math.round(255 * 0.5).toString(16)}`;
    
    var ctx = document.getElementById(container_id).getContext('2d');
    
    var myChart = new Chart(ctx, {
        type: 'bar', // You can change this to 'bar', 'pie', etc.
        data: {
            labels: keys,
            datasets: [{
                label: labelText,
                data: values, //Array(values.length).fill(0),
                backgroundColor: transparentChartColor,
                borderColor: chartColor,
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: `${labelText} per year`
                }
            }
        }
    });
}