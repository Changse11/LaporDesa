/* ===== ADMIN SCRIPTS ===== */

/* Pagination: goPage function for admin pages */
function goPage(p) {
  const url = new URL(window.location.href);
  url.searchParams.set("page", p);
  window.location.href = url.toString();
}

/* Override tab-btn style for anchor tags (laporan.php) */
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.style.display = "inline-block";
    btn.style.textDecoration = "none";
  });
});

/* Laporan action handler (detail-laporan-belum.php) */
function aksiLaporan(aksi) {
  const prioritasSelect = document.getElementById("prioritasSelect");

  if (aksi === "terima") {
    if (prioritasSelect.value === "") {
      prioritasSelect.focus();
      prioritasSelect.style.borderColor = "#e53e3e";
      alert("Pilih prioritas penanganan terlebih dahulu.");
      return;
    }
    if (
      !confirm(
        "Terima dan proses laporan ini dengan prioritas " +
          prioritasSelect.value +
          "?",
      )
    )
      return;
  } else {
    if (
      !confirm(
        "Tolak laporan ini? Pastikan Anda sudah menambahkan alasan di komentar.",
      )
    )
      return;
  }

  document.getElementById("inputAksi").value = aksi;
  document.getElementById("formLaporan").submit();
}

/* Reset border merah saat prioritas dipilih */
document.addEventListener("DOMContentLoaded", function () {
  const prioritasSelect = document.getElementById("prioritasSelect");
  if (prioritasSelect) {
    prioritasSelect.addEventListener("change", function () {
      this.style.borderColor = "";
    });
  }
});
