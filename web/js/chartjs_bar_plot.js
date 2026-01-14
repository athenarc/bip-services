function render_bar_plot(container_id, keys, values, labelText, yAxisLabel, xAxisLabel) {
    const chartColor = getComputedStyle(document.documentElement).getPropertyValue('--main-color');
    const transparentChartColor = `${chartColor}${Math.round(255 * 0.5).toString(16)}`;

    const ctx = document.getElementById(container_id).getContext('2d');

    const myChart = new Chart(ctx, {
        type: 'bar', // You can change this to 'bar', 'pie', etc.
        data: {
            labels: keys,
            datasets: [{
                label: labelText,
                data: values, // Array(values.length).fill(0),
                backgroundColor: transparentChartColor,
                borderColor: chartColor,
                borderWidth: 1,
            }],
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: yAxisLabel ? true : false,
                        text: yAxisLabel || '',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    title: {
                        display: xAxisLabel ? true : false,
                        text: xAxisLabel || '',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
            },
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                    position: 'bottom',
                },
                title: {
                    display: false,
                },
            },
        },
    });
}
