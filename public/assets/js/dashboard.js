const palette = [
  "#4375df",
  "#49a4a4",
  "#7f82ce",
  "#e0b367",
  "#7da2bf",
  "#94b895",
];
const number = new Intl.NumberFormat("id-ID");
const monthName = (value) =>
  new Intl.DateTimeFormat("id-ID", { month: "long", year: "numeric" }).format(
    new Date(`${value}-01T12:00:00`),
  );
const compactMonth = (value) =>
  new Intl.DateTimeFormat("id-ID", { month: "short" }).format(
    new Date(`${value}-01T12:00:00`),
  );
const percentage = (value, total) =>
  total ? `${number.format(Math.round((value * 1000) / total) / 10)}%` : "0%";

document.addEventListener("DOMContentLoaded", () => {
  if (!document.querySelector("#movement-chart")) return;
  const range = document.querySelector("#movement-range");
  const feedback = document.querySelector("#dashboard-feedback");
  const status = document.querySelector("#dashboard-status");
  const retry = document.querySelector("#dashboard-retry");
  const charts = document.querySelector("#dashboard-charts");
  const reducedMotion = matchMedia("(prefers-reduced-motion: reduce)").matches;
  let movement;
  let controller;
  let initialized = false;

  const drillDown = (key, values) => (_event, elements, chart) => {
    if (!elements.length) return;
    const target = `/barang?${new URLSearchParams({ [key]: values[elements[0].index] })}`;
    if (reducedMotion) {
      location.href = target;
      return;
    }
    chart.setActiveElements(elements);
    chart.update("none");
    window.setTimeout(() => {
      location.href = target;
    }, 90);
  };
  const hover = (event, elements) => {
    event.native.target.style.cursor = elements.length ? "pointer" : "default";
  };

  function initializeComposition(data) {
    const quantities = data.category.map((row) => Number(row.total));
    const total = quantities.reduce((sum, value) => sum + value, 0);
    new Chart(document.querySelector("#category-chart"), {
      type: "doughnut",
      data: {
        labels: data.category.map((row) => row.kategori),
        datasets: [
          {
            data: quantities,
            backgroundColor: palette,
            borderWidth: 4,
            borderColor: "#fff",
            hoverOffset: 8,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "77%",
        animation: { duration: reducedMotion ? 0 : 450 },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) =>
                `${number.format(context.parsed)} unit · ${percentage(context.parsed, total)} dari total stok`,
            },
          },
        },
        onClick: drillDown(
          "kategori",
          data.category.map((row) => row.kategori),
        ),
        onHover: hover,
      },
    });
  }

  function initializeWarehouse(data) {
    new Chart(document.querySelector("#warehouse-chart"), {
      type: "bar",
      data: {
        labels: data.warehouses.map((row) =>
          row.nama_gudang.replace(/^Gudang /, ""),
        ),
        datasets: [
          {
            data: data.warehouses.map((row) => Number(row.total)),
            backgroundColor: ["#5984df", "#81a4eb", "#b3c8ef"],
            hoverBackgroundColor: "#356ae6",
            borderRadius: 4,
            maxBarThickness: 16,
          },
        ],
      },
      options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: reducedMotion ? 0 : 400 },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              title: (contexts) =>
                data.warehouses[contexts[0].dataIndex].nama_gudang,
              label: (context) =>
                `Total stok: ${number.format(context.parsed.x)} unit`,
              afterLabel: (context) =>
                `Jenis barang: ${number.format(data.warehouses[context.dataIndex].items)}`,
            },
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            grid: { color: "#eef2f8" },
            border: { display: false },
            ticks: { font: { size: 9 }, maxTicksLimit: 5 },
          },
          y: {
            grid: { display: false },
            border: { display: false },
            ticks: { font: { size: 10 } },
          },
        },
        onClick: drillDown(
          "gudang_id",
          data.warehouses.map((row) => String(row.id)),
        ),
        onHover: hover,
      },
    });
  }

  function initializeHealth(data) {
    const total = data.health.reduce((sum, row) => sum + Number(row.total), 0);
    new Chart(document.querySelector("#health-chart"), {
      type: "doughnut",
      data: {
        labels: data.health.map((row) => row.label),
        datasets: [
          {
            data: data.health.map((row) => Number(row.total)),
            backgroundColor: ["#4caa88", "#e1b461", "#d98690"],
            borderWidth: 4,
            borderColor: "#fff",
            hoverOffset: 8,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "78%",
        animation: { duration: reducedMotion ? 0 : 450 },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) =>
                `${number.format(context.parsed)} barang · ${percentage(context.parsed, total)} dari seluruh barang`,
            },
          },
        },
        onClick: drillDown(
          "status",
          data.health.map((row) => row.key),
        ),
        onHover: hover,
      },
    });
  }

  function updateMovement(data) {
    const datasets = [
      {
        label: "Barang masuk",
        data: data.series.barang_masuk,
        backgroundColor: "#4e7dea",
        hoverBackgroundColor: "#356ae6",
        borderRadius: 4,
        maxBarThickness: 23,
      },
      {
        label: "Barang keluar",
        data: data.series.barang_keluar,
        backgroundColor: "#b8ccef",
        hoverBackgroundColor: "#8aade9",
        borderRadius: 4,
        maxBarThickness: 23,
      },
    ];
    if (movement) {
      movement.data.labels = data.months;
      movement.data.datasets = datasets;
      movement.update();
    } else {
      movement = new Chart(document.querySelector("#movement-chart"), {
        type: "bar",
        data: { labels: data.months, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: { duration: reducedMotion ? 0 : 450 },
          interaction: { mode: "index", intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                title: (contexts) => monthName(contexts[0].label),
                label: (context) =>
                  `${context.dataset.label}: ${number.format(context.parsed.y)} unit`,
                footer: (contexts) => {
                  const incoming = contexts[0].parsed.y;
                  const outgoing = contexts[1].parsed.y;
                  const net = incoming - outgoing;
                  return `Net: ${net > 0 ? "+" : ""}${number.format(net)} unit`;
                },
              },
            },
          },
          scales: {
            x: {
              grid: { display: false },
              border: { display: false },
              ticks: {
                color: "#7e90ac",
                font: { size: 10 },
                callback: function (value) {
                  return compactMonth(this.getLabelForValue(value));
                },
              },
            },
            y: {
              beginAtZero: true,
              border: { display: false },
              grid: { color: "#eef2f8" },
              ticks: { font: { size: 9 }, maxTicksLimit: 5, precision: 0 },
            },
          },
        },
      });
    }
    document.querySelector("#movement-period").textContent =
      `${monthName(data.months[0])} – ${monthName(data.months.at(-1))}`;
    const table = document.querySelector("#movement-data");
    table.replaceChildren();
    data.months.forEach((month, index) => {
      const row = document.createElement("tr");
      [
        monthName(month),
        data.series.barang_masuk[index],
        data.series.barang_keluar[index],
        data.series.barang_masuk[index] - data.series.barang_keluar[index],
      ].forEach((value) => {
        const cell = document.createElement("td");
        cell.textContent = String(value);
        row.append(cell);
      });
      table.append(row);
    });
  }

  async function load() {
    controller?.abort();
    controller = new AbortController();
    const requestController = controller;
    charts.setAttribute("aria-busy", "true");
    feedback.classList.remove("error");
    status.textContent = "Memuat data grafik…";
    retry.hidden = true;
    try {
      if (!window.Chart)
        throw new Error("Library grafik belum tersedia. Muat ulang halaman.");
      const response = await fetch(
        `/api/v1/dashboard?range=${encodeURIComponent(range.value)}`,
        {
          headers: { Accept: "application/json" },
          signal: requestController.signal,
        },
      );
      const result = await response.json();
      if (!response.ok) throw new Error(result.message);
      Chart.defaults.font.family = "'Segoe UI', sans-serif";
      Chart.defaults.color = "#7589a7";
      Chart.defaults.plugins.tooltip.backgroundColor = "#1f3453";
      Chart.defaults.plugins.tooltip.padding = 13;
      Chart.defaults.plugins.tooltip.cornerRadius = 8;
      Chart.defaults.plugins.tooltip.titleFont = { size: 13, weight: "600" };
      Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
      Chart.defaults.plugins.tooltip.titleMarginBottom = 7;
      Chart.defaults.plugins.tooltip.bodySpacing = 5;
      updateMovement(result.data);
      if (!initialized) {
        initializeComposition(result.data);
        initializeWarehouse(result.data);
        initializeHealth(result.data);
        initialized = true;
      }
      const hasMovement = [
        ...result.data.series.barang_masuk,
        ...result.data.series.barang_keluar,
      ].some((value) => value > 0);
      status.textContent = hasMovement
        ? "Data grafik terbaru · klik kategori, gudang, atau status untuk menelusuri barang."
        : "Belum ada transaksi pada periode ini. Pilih periode lain atau catat transaksi baru.";
    } catch (error) {
      if (error.name === "AbortError") return;
      feedback.classList.add("error");
      status.textContent = "Grafik belum dapat dimuat. " + error.message;
      retry.hidden = false;
    } finally {
      if (controller === requestController && !requestController.signal.aborted)
        charts.setAttribute("aria-busy", "false");
    }
  }
  range.addEventListener("change", load);
  retry.addEventListener("click", load);
  if (!reducedMotion) {
    const started = performance.now();
    const duration = 650;
    const values = [...document.querySelectorAll(".metric-value")];
    values.forEach((element) => {
      element.textContent = "0";
    });
    const count = (time) => {
      const progress = Math.min((time - started) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      values.forEach((element) => {
        element.textContent = number.format(
          Math.round(Number(element.dataset.value) * eased),
        );
      });
      if (progress < 1) requestAnimationFrame(count);
    };
    requestAnimationFrame(count);
  }
  load();
});
