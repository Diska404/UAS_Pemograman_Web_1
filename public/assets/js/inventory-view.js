import { tableOptions } from "./app.js";

document.addEventListener("DOMContentLoaded", () => {
  const table = document.querySelector("#inventory-table");
  if (!table || !window.DataTable) return;
  const panel = document.querySelector("#inventory-panel");
  const gallery = document.querySelector("#product-gallery");
  const cards = [...gallery.querySelectorAll(".product-card")];
  const empty = document.querySelector("#gallery-empty");
  const buttons = [...document.querySelectorAll("[data-view]")];
  const dataTable = new DataTable(table, {
    ...tableOptions,
    order: [[1, "asc"]],
    pageLength: 12,
    lengthMenu: [12, 24, 60],
    autoWidth: false,
    search: { search: "" },
  });
  const wrapper = document.querySelector("#inventory-table_wrapper");
  const scrollArea = wrapper.querySelector(".dt-scroll");
  scrollArea.after(gallery);
  const sortLabel = document.createElement("label");
  sortLabel.className = "inventory-sort";
  sortLabel.textContent = "Urutkan barang";
  const sort = document.createElement("select");
  sort.className = "form-select";
  [
    ["1,asc", "Kode A–Z"],
    ["2,asc", "Nama A–Z"],
    ["5,asc", "Stok terendah"],
    ["5,desc", "Stok tertinggi"],
  ].forEach(([value, label]) => {
    const option = document.createElement("option");
    option.value = value;
    option.textContent = label;
    sort.append(option);
  });
  sortLabel.append(sort);
  gallery.before(sortLabel);
  sort.addEventListener("change", () => {
    const [column, direction] = sort.value.split(",");
    dataTable.order([Number(column), direction]).draw();
  });
  dataTable.on("order", () => {
    const [column, direction] = dataTable.order()[0];
    sort.value = `${column},${direction}`;
  });
  const drawGallery = () => {
    const visible = dataTable
      .rows({ page: "current", search: "applied", order: "applied" })
      .nodes()
      .toArray()
      .map((row) => row.dataset.itemId);
    const order = new Map(visible.map((id, index) => [id, index]));
    cards.forEach((card) => {
      card.hidden = !order.has(card.dataset.itemId);
    });
    visible.forEach((id) => {
      const card = cards.find((node) => node.dataset.itemId === id);
      if (card) gallery.append(card);
    });
    empty.hidden = visible.length !== 0;
    gallery.append(empty);
  };
  const setView = (view) => {
    const isGallery = view === "gallery";
    panel.classList.toggle("gallery-mode", isGallery);
    gallery.hidden = !isGallery;
    buttons.forEach((button) => {
      const active = button.dataset.view === (isGallery ? "gallery" : "table");
      button.classList.toggle("active", active);
      button.setAttribute("aria-pressed", String(active));
    });
    drawGallery();
    if (!isGallery) dataTable.columns.adjust();
    try {
      localStorage.setItem(
        "sim.inventory.view",
        isGallery ? "gallery" : "table",
      );
    } catch {}
  };
  let preference = "table";
  try {
    preference = localStorage.getItem("sim.inventory.view") || preference;
  } catch {}
  const requested = new URLSearchParams(location.search).get("view");
  if (requested === "table" || requested === "gallery") preference = requested;
  buttons.forEach((button) =>
    button.addEventListener("click", () => setView(button.dataset.view)),
  );
  dataTable.on("draw", drawGallery);
  setView(preference);
  document
    .querySelector("#inventory-filters")
    .addEventListener("submit", () => {
      panel.setAttribute("aria-busy", "true");
      const submit = document.querySelector(
        '#inventory-filters button[type="submit"]',
      );
      submit.disabled = true;
      submit.setAttribute("aria-label", "Menerapkan filter");
    });
});
