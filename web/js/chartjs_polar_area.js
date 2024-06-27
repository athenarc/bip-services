
function getLabelForValue(i) {
    const labels = ["", "", "Top 10%", "Top 1%", "Top 0.1%", "Top 0.01%"]
    return labels[i];
}

Chart.register(ChartDataLabels);


function render_polar_area_chart(container_id, title, data, tooltips, color) {
    const defaultColor = getComputedStyle(document.documentElement).getPropertyValue('--main-color');
    const chartColor = color ? color : defaultColor;
    const transparentChartColor = `${chartColor}${Math.round(255 * 0.5).toString(16)}`;
    const ctx = document.getElementById(container_id).getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'polarArea',
        data: {
            datasets: [{
                data,
                icons: [
                    // unicode of fontawesome icons
                    '\uf06d',  '\uf19c', '\uf10d', '\uf135',
                ],
                backgroundColor: [
                    transparentChartColor,
                    transparentChartColor,
                    transparentChartColor,
                    transparentChartColor,
                ],
            }],
            labels: [
                'Popularity',
                'Influence',
                'Citation Count',
                'Impulse'
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,

            startAngle: -0.509 * Math.PI,

            plugins: {
                title: {
                    display: (title),
                    text: title,
                    color: chartColor
                },
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return "  In " + tooltips[context.dataIndex];
                        }
                    }
                },
                datalabels: {
                    formatter: (value, context) => context.dataset.icons[context.dataIndex],
                    font: {
                        family: 'FontAwesome',
                        size: 16,
                    },
                    anchor: "start",
                    align: "end",
                    textAlign: "center",
                    offset: 20,  // Gets updated in onResize based on size of chart
                },
            },
            scales: {
                r: {
                    min: 0,
                    max: 5,
                    ticks: {
                        callback: function(value, index, ticks) {
                            return getLabelForValue(value);
                        },
                        stepSize: 1
                    },
                },

            },
            elements: {
                arc: {
                    borderColor: chartColor,
                    borderWidth : 1.5,
                },

            },

            animation: {
                // updates the chart one time, needed to fix the shape of the icons
                onProgress: function({ chart, initial}) {
                    if (initial) {
                        chart.update('none');
                    }

                }
            }
        }
    });
}