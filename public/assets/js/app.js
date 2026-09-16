export const tableOptions = {
  pageLength: 10,
  order: [[0, "desc"]],
  scrollX: true,
  language: {
    search: "Cari di hasil:",
    lengthMenu: "Tampilkan _MENU_ data",
    info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
    infoEmpty: "Belum ada data",
    emptyTable: "Belum ada data yang sesuai. Tambahkan data atau ubah filter.",
    zeroRecords: "Tidak ada data yang sesuai pencarian.",
    infoFiltered: "(dari _MAX_ data)",
    paginate: { first: "Awal", last: "Akhir", next: "→", previous: "←" },
    aria: {
      orderable: "Urutkan kolom ini",
      orderableReverse: "Balik urutan kolom",
      orderableRemove: "Hapus pengurutan",
    },
  },
};

function initializeShell() {
  const sidebar = document.querySelector("#sidebar");
  const toggle = document.querySelector("#menu-toggle");
  if (!sidebar || !toggle) return;
  const workspace = document.querySelector("#workspace");
  const backdrop = document.querySelector("#sidebar-backdrop");
  const mobile = matchMedia("(max-width: 900px)");
  let collapsed = false;
  try {
    collapsed = localStorage.getItem("sim.sidebar.collapsed") === "true";
  } catch {}
  const update = (open = false) => {
    document.body.classList.toggle(
      "sidebar-collapsed",
      !mobile.matches && collapsed,
    );
    document.body.classList.toggle("sidebar-open", mobile.matches && open);
    sidebar.inert = mobile.matches && !open;
    workspace.inert = mobile.matches && open;
    backdrop.hidden = !(mobile.matches && open);
    toggle.setAttribute(
      "aria-expanded",
      String(mobile.matches ? open : !collapsed),
    );
    toggle.setAttribute(
      "aria-label",
      mobile.matches
        ? "Buka navigasi"
        : collapsed
          ? "Perluas navigasi"
          : "Ciutkan navigasi",
    );
    if (mobile.matches && open) {
      sidebar.setAttribute("role", "dialog");
      sidebar.setAttribute("aria-modal", "true");
    } else {
      sidebar.removeAttribute("role");
      sidebar.removeAttribute("aria-modal");
    }
    window.dispatchEvent(new Event("resize"));
  };
  const close = () => {
    update(false);
    toggle.focus();
  };
  toggle.addEventListener("click", () => {
    if (mobile.matches) {
      update(true);
      document.querySelector("#sidebar-close").focus();
    } else {
      collapsed = !collapsed;
      try {
        localStorage.setItem("sim.sidebar.collapsed", String(collapsed));
      } catch {}
      update();
    }
  });
  document.querySelector("#sidebar-close").addEventListener("click", close);
  backdrop.addEventListener("click", close);
  sidebar.addEventListener("keydown", (event) => {
    if (!mobile.matches || !document.body.classList.contains("sidebar-open"))
      return;
    if (event.key === "Escape") {
      event.preventDefault();
      close();
    }
    if (event.key === "Tab") {
      const controls = [...sidebar.querySelectorAll("a,button")].filter(
        (el) => el.getClientRects().length && !el.disabled,
      );
      const first = controls[0],
        last = controls[controls.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      }
      if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });
  sidebar.querySelectorAll("a").forEach((link) =>
    link.addEventListener("click", () => {
      if (mobile.matches && link.hash) close();
    }),
  );
  mobile.addEventListener("change", () => update());
  update();
}

function initializeFeedback() {
  const flash = document.querySelector("#flash");
  if (flash && window.Swal) {
    flash.hidden = true;
    const success = flash.dataset.type === "success";
    Swal.fire({
      icon: flash.dataset.type,
      title: success ? "Berhasil" : "Periksa kembali",
      text: flash.dataset.message,
      confirmButtonColor: "#356ae6",
      toast: success,
      position: success ? "top-end" : "center",
      timer: success ? 4500 : undefined,
      timerProgressBar: success,
      showConfirmButton: !success,
    });
  }
  document.addEventListener("submit", async (event) => {
    const form = event.target;
    if (!form.matches(".delete-form")) return;
    event.preventDefault();
    const confirmed = window.Swal
      ? (
          await Swal.fire({
            title: "Hapus data ini?",
            text: "Data yang masih digunakan tidak dapat dihapus. Koreksi transaksi akan memperbarui stok.",
            icon: "warning",
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: "Ya, hapus",
            cancelButtonText: "Batal",
            confirmButtonColor: "#b74653",
          })
        ).isConfirmed
      : confirm("Hapus data ini?");
    if (confirmed) {
      form.querySelector('button[type="submit"]').disabled = true;
      form.submit();
    }
  });
}

function initializePasswordToggles() {
  document.querySelectorAll(".password-toggle").forEach((button) => {
    const input = document.getElementById(button.getAttribute("aria-controls"));
    if (!input) return;
    button.addEventListener("click", () => {
      const reveal = input.type === "password";
      input.type = reveal ? "text" : "password";
      button.textContent = reveal ? "Sembunyikan" : "Tampilkan";
      button.setAttribute(
        "aria-label",
        `${reveal ? "Sembunyikan" : "Tampilkan"} ${input.id === "password_confirmation" ? "konfirmasi password" : "password"}`,
      );
      input.focus({ preventScroll: true });
      const end = input.value.length;
      input.setSelectionRange(end, end);
    });
  });
}

function initializeRememberedEmail() {
  const checkbox = document.querySelector("#remember-email");
  const email = document.querySelector('form[action="/login"] #email');
  const form = email?.form;
  if (!checkbox || !email || !form) return;
  const key = "simInventory.rememberedEmail";
  try {
    const remembered = localStorage.getItem(key);
    if (remembered) {
      if (!email.value) email.value = remembered;
      checkbox.checked = email.value.trim() === remembered;
    }
  } catch {}
  form.addEventListener("submit", () => {
    try {
      if (checkbox.checked && email.validity.valid)
        localStorage.setItem(key, email.value.trim());
      else localStorage.removeItem(key);
    } catch {}
  });
}

function initializeSubmitFeedback() {
  document.addEventListener("submit", (event) => {
    const form = event.target;
    if (
      !(form instanceof HTMLFormElement) ||
      form.matches(".delete-form") ||
      event.defaultPrevented
    )
      return;
    const button = form.querySelector(
      'button[type="submit"], button:not([type])',
    );
    if (!button || button.disabled) return;
    form.setAttribute("aria-busy", "true");
    button.disabled = true;
    button.classList.add("is-submitting");
    const label = button.querySelector(".button-label");
    const loadingLabel = button.dataset.loadingLabel || "Menyimpan…";
    if (label) label.textContent = loadingLabel;
    else button.textContent = loadingLabel;
  });
}

function initializeStockLookup() {
  const item = document.querySelector("select#barang_id");
  const hint = document.querySelector("#stock-hint");
  if (!item || !hint) return;
  let controller;
  const update = async () => {
    controller?.abort();
    if (!item.value) {
      hint.textContent = "Pilih barang untuk melihat stok tersedia.";
      return;
    }
    controller = new AbortController();
    hint.textContent = "Memuat stok…";
    try {
      const response = await fetch(
        `/api/v1/barang/${encodeURIComponent(item.value)}`,
        { headers: { Accept: "application/json" }, signal: controller.signal },
      );
      const result = await response.json();
      if (!response.ok) throw new Error(result.message);
      hint.textContent = `Stok tersedia: ${result.data.stok} ${result.data.satuan} · Minimum: ${result.data.stok_minimum}`;
    } catch (error) {
      if (error.name !== "AbortError")
        hint.textContent = "Stok belum dapat dimuat. " + error.message;
    }
  };
  item.addEventListener("change", update);
  update();
}

document.addEventListener("DOMContentLoaded", () => {
  initializeShell();
  initializeFeedback();
  initializePasswordToggles();
  initializeRememberedEmail();
  initializeSubmitFeedback();
  initializeStockLookup();
  if (window.DataTable)
    document
      .querySelectorAll(".data-table")
      .forEach((table) => new DataTable(table, tableOptions));
  document.querySelectorAll("img[data-fallback]").forEach((img) => {
    const fallback = () => {
      if (img.dataset.fallback) {
        img.src = img.dataset.fallback;
        delete img.dataset.fallback;
      }
    };
    img.addEventListener("error", fallback, { once: true });
    if (img.complete && !img.naturalWidth) fallback();
  });
});
