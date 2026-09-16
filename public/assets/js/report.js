const source = document.querySelector("#report-analytics-data");

if (source && window.Chart) {
  const analytics = JSON.parse(source.textContent);
  const reducedMotion = matchMedia("(prefers-reduced-motion: reduce)").matches;
  const palette = ["#356ae6", "#26a69a", "#f2a33a", "#b74653", "#7857c8", "#4e86ad"];
  const number = new Intl.NumberFormat("id-ID");

  Chart.defaults.font.family = "'Segoe UI', sans-serif";
  Chart.defaults.color = "#7589a7";
  Chart.defaults.animation.duration = reducedMotion ? 0 : 420;
  Chart.defaults.plugins.tooltip.backgroundColor = "#1f3453";
  Chart.defaults.plugins.tooltip.padding = 12;
  Chart.defaults.plugins.tooltip.cornerRadius = 8;

  analytics.charts.forEach((summary) => {
    const canvas = document.getElementById(summary.id);
    if (!canvas || !summary.values.length) return;
    const doughnut = summary.kind === "doughnut";
    const horizontal = summary.kind === "bar-horizontal";
    const line = summary.kind === "line";
    const colors = doughnut ? palette.slice(0, summary.values.length) : palette[0];

    const scales = doughnut
      ? {}
      : horizontal
        ? {
            x: { beginAtZero: true, grid: { color: "rgba(94, 116, 148, .10)" }, ticks: { precision: 0 } },
            y: { grid: { display: false }, ticks: { autoSkip: false, callback(value) { const label = String(this.getLabelForValue(value)); return label.length > 24 ? `${label.slice(0, 23)}…` : label; } } },
          }
        : {
            x: { grid: { display: false }, ticks: { maxRotation: line ? 35 : 0 } },
            y: { beginAtZero: true, grid: { color: "rgba(94, 116, 148, .10)" }, ticks: { precision: 0 } },
          };

    new Chart(canvas, {
      type: doughnut ? "doughnut" : line ? "line" : "bar",
      data: {
        labels: summary.labels,
        datasets: [{
          label: "Unit",
          data: summary.values,
          backgroundColor: line ? "rgba(53, 106, 230, .13)" : colors,
          borderColor: line ? "#356ae6" : doughnut ? "#ffffff" : colors,
          borderWidth: line ? 2 : doughnut ? 3 : 0,
          borderRadius: doughnut ? 0 : 6,
          pointBackgroundColor: "#356ae6",
          pointRadius: line ? 3 : 0,
          fill: line,
          tension: line ? 0.32 : 0,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: horizontal ? "y" : "x",
        cutout: doughnut ? "66%" : undefined,
        interaction: { intersect: false, mode: "nearest" },
        plugins: {
          legend: { display: doughnut, position: "bottom", labels: { usePointStyle: true, pointStyle: "circle", boxWidth: 8, padding: 14 } },
          tooltip: { callbacks: { label: (context) => `${context.label || context.dataset.label}: ${number.format(context.raw)} unit` } },
        },
        scales,
      },
    });
  });
}
