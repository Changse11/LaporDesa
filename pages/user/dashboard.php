<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

// ── Query statistik global ─────────────────────────────────────
$total = $conn->query("SELECT COUNT(*) AS c FROM laporan")->fetch_assoc()['c'];

$bulan_ini = $conn->query("
    SELECT COUNT(*) AS c FROM laporan
    WHERE MONTH(tanggal_laporan) = MONTH(CURDATE())
      AND YEAR(tanggal_laporan)  = YEAR(CURDATE())
")->fetch_assoc()['c'];

$hari_ini = $conn->query("
    SELECT COUNT(*) AS c FROM laporan
    WHERE DATE(tanggal_laporan) = CURDATE()
")->fetch_assoc()['c'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard – LaporDesa</title>
  <link rel="stylesheet" href="../../assets/css/user.css"/>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<main>
  <div class="dash-wrapper">

    <div class="dash-header animate">
      <h1>Dashboard Statistik Laporan</h1>
      <p>Ringkasan data laporan pengaduan dan aspirasi warga yang telah masuk ke sistem LaporDesa.</p>
    </div>

    <!-- Total Keseluruhan -->
    <div class="stat-main animate delay-1">
      <div class="icon-wrap">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="3" width="20" height="14" rx="2"/>
          <line x1="8" y1="21" x2="16" y2="21"/>
          <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
      </div>
      <div class="stat-label">Jumlah Laporan Keseluruhan</div>
      <div class="stat-value"><?= number_format($total, 0, ',', '.') ?></div>
      <div class="stat-note">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
          <polyline points="17 6 23 6 23 12"/>
        </svg>
        Total sejak sistem diluncurkan
      </div>
    </div>

    <!-- Bulan Ini & Hari Ini -->
    <div class="stat-row animate delay-2">

      <div class="stat-card">
        <div class="icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
            <rect x="7" y="14" width="4" height="4" rx="1"/>
          </svg>
        </div>
        <div class="stat-label">Jumlah Laporan Bulan ini</div>
        <div class="stat-value"><?= number_format($bulan_ini, 0, ',', '.') ?></div>
      </div>

      <div class="stat-card">
        <div class="icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
            <line x1="12" y1="14" x2="12" y2="18"/>
            <line x1="10" y1="16" x2="14" y2="16"/>
          </svg>
        </div>
        <div class="stat-label">Jumlah Laporan Hari ini</div>
        <div class="stat-value"><?= number_format($hari_ini, 0, ',', '.') ?></div>
      </div>

    </div>

  </div>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>