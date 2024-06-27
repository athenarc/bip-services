
const chartColor = getComputedStyle(document.documentElement).getPropertyValue('--main-color');
const transparentChartColor = `${chartColor}${Math.round(255 * 0.5).toString(16)}`;

Chart.defaults.borderColor = transparentChartColor;
function render_radar_chart(container_id, data, labels) {
  const ctx = document.getElementById(container_id).getContext('2d');
  const myChart = new Chart(ctx, {
      type: 'radar',
      data: {
        labels: labels,
        datasets: [{
          data: data,
          fill: true,
          backgroundColor: transparentChartColor,
          borderColor: chartColor,
          pointBackgroundColor: chartColor,
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: chartColor
        }]
      },
      options: {
        plugins: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            display: false,
          },
          datalabels: {
            display: false
          },
          tooltip: {

            displayColors: false
         },
        },
        scales: {
          r: {
              beginAtZero: true,
              ticks: {
                callback: function (value) { if (Number.isInteger(value)) { return value; } },
              }
          }
        }
      }
  });
}