function render_admin_bar_plot(container_id, keys, values, labelText) {

    const chartColor = getComputedStyle(document.documentElement).getPropertyValue('--main-color');
    const transparentChartColor = `${chartColor}${Math.round(255 * 0.5).toString(16)}`;

    const ctx = document.getElementById(container_id).getContext('2d');

    const myChart = new Chart(ctx, {
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

                    min: Math.min(...values) > 0 ? Math.min(...values) - 1 : 0,
                    beginAtZero: false,
                    ticks: {
                        stepSize: 1
                    },
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
                    text: `${labelText} per month`
                }
            }
        }
    });
}

