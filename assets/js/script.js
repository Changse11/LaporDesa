// ── Avatar dropdown ────────────────────────────────────────────
function toggleDropdown() {
  const el = document.getElementById("avatarDropdown");
  if (el) el.classList.toggle("show");
}

document.addEventListener("click", function (e) {
  const wrap = document.querySelector(".avatar-wrap");
  if (!wrap) return;
  if (!wrap.contains(e.target)) {
    const el = document.getElementById("avatarDropdown");
    if (el) el.classList.remove("show");
  }
});

// ── Burger menu ────────────────────────────────────────────────
function toggleBurger() {
  const menu = document.getElementById("navMenu");
  const btn = document.getElementById("burgerBtn");
  const overlay = document.getElementById("navOverlay");
  if (!menu) return;

  const isOpen = menu.classList.toggle("open");
  btn.classList.toggle("open", isOpen);
  overlay.classList.toggle("show", isOpen);
  // Cegah scroll body saat menu terbuka
  document.body.style.overflow = isOpen ? "hidden" : "";
}

function closeBurger() {
  const menu = document.getElementById("navMenu");
  const btn = document.getElementById("burgerBtn");
  const overlay = document.getElementById("navOverlay");
  if (!menu) return;

  menu.classList.remove("open");
  btn.classList.remove("open");
  overlay.classList.remove("show");
  document.body.style.overflow = "";
}

// Tutup menu saat link diklik (navigasi mobile)
document.addEventListener("DOMContentLoaded", function () {
  const links = document.querySelectorAll(".navbar-nav a");
  links.forEach((link) => link.addEventListener("click", closeBurger));

  const jenisInput = document.getElementById("inputJenis");
  if (jenisInput) {
    setTab(jenisInput.value || "Pengaduan");
  }
});

// ── Tab switching (laporan-form.php) ──────────────────────────────────
function setTab(type) {
  document
    .getElementById("tab-pengaduan")
    .classList.toggle("active", type === "Pengaduan");
  document
    .getElementById("tab-aspirasi")
    .classList.toggle("active", type === "Aspirasi");
  document.getElementById("inputJenis").value = type;

  const isPengaduan = type === "Pengaduan";
  document.querySelectorAll(".pengaduan-only").forEach((el) => {
    el.style.display = isPengaduan
      ? el.classList.contains("pengaduan-row")
        ? "grid"
        : "block"
      : "none";
  });

  document.body.className = "tab-" + type.toLowerCase();

  document.getElementById("label-judul").textContent = isPengaduan
    ? "Ketik Judul Laporan Anda"
    : "Ketik Judul Aspirasi Anda";
  document.getElementById("label-isi").textContent = isPengaduan
    ? "Ketik Isi Laporan Anda"
    : "Ketik Isi Aspirasi Anda";
  document.getElementById("judul").placeholder = isPengaduan
    ? "Contoh: Jalan berlubang di RT 03"
    : "Contoh: Usulan pembangunan taman bermain";
  document.getElementById("isi").placeholder = isPengaduan
    ? "Deskripsikan kejadian atau laporan secara detail..."
    : "Sampaikan ide atau saran Anda untuk kemajuan desa...";
}

// ── File handling (laporan-form.php) ──────────────────────────────────
function handleFile(file) {
  if (!file) return;

  const allowed = ["image/jpeg", "image/png", "application/pdf"];
  const maxSize = 5 * 1024 * 1024; // 5MB

  if (!allowed.includes(file.type)) {
    alert("Format file tidak didukung. Gunakan JPG, PNG, atau PDF.");
    clearFile();
    return;
  }
  if (file.size > maxSize) {
    alert("Ukuran file melebihi 5MB.");
    clearFile();
    return;
  }

  // Tampilkan info file
  document.getElementById("fileNameDisplay").textContent = file.name;
  document.getElementById("fileSizeDisplay").textContent = formatSize(
    file.size,
  );
  document.getElementById("filePreview").style.display = "flex";
  document.getElementById("uploadArea").style.opacity = "0.6";
}

function clearFile() {
  document.getElementById("lampiran").value = "";
  document.getElementById("filePreview").style.display = "none";
  document.getElementById("uploadArea").style.opacity = "1";
}

function formatSize(bytes) {
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
  return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

// ── Drag & Drop (laporan-form.php) ────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  const uploadArea = document.getElementById("uploadArea");
  if (uploadArea) {
    uploadArea.addEventListener("dragover", (e) => {
      e.preventDefault();
      uploadArea.classList.add("dragover");
    });
    uploadArea.addEventListener("dragleave", () => {
      uploadArea.classList.remove("dragover");
    });
    uploadArea.addEventListener("drop", (e) => {
      e.preventDefault();
      uploadArea.classList.remove("dragover");
      const file = e.dataTransfer.files[0];
      if (file) {
        // Set file ke input
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById("lampiran").files = dt.files;
        handleFile(file);
      }
    });
  }
});
