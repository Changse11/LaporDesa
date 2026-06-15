<?php
include '../lib/koneksi.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();
include '../includes/nav.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tentang – LaporKades</title>
  <link rel="stylesheet" href="../../assets/css/user.css"/>
  
</head>
<body>
  <main>

    <div class="tentang-header animate">
      <h1>Tentang LaporKades</h1>
      <p>LaporKades adalah sistem pengaduan dan aspirasi rakyat yang dibuat khusus untuk masyarakat Desa Kutapohaci. Platform ini hadir sebagai jembatan komunikasi antara warga dan pemerintah desa, agar setiap suara dapat didengar dan ditindaklanjuti dengan cepat, transparan, serta akuntabel.</p>
    </div>

    <div class="visi-tujuan animate delay-1">
      <div class="vt-card">
        <div class="vt-icon icon-green">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </div>
        <h3>Visi</h3>
        <p>Mewujudkan Desa Kutapohaci yang lebih terbuka, partisipatif, dan responsif terhadap kebutuhan serta aspirasi masyarakat.</p>
      </div>

      <div class="vt-card">
        <div class="vt-icon icon-mint">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
            <line x1="4" y1="22" x2="4" y2="15"/>
          </svg>
        </div>
        <h3>Tujuan</h3>
        <p>Dengan adanya LaporKades, diharapkan setiap warga Desa Kutapohaci dapat berperan aktif dalam membangun desa. Setiap laporan, kritik, maupun saran akan menjadi bahan evaluasi dan perbaikan demi kesejahteraan bersama.</p>
      </div>
    </div>

    <div class="misi-card animate delay-2">
      <div class="misi-header">
        <div class="vt-icon icon-brown" style="margin-bottom:0">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
          </svg>
        </div>
        <h3>Misi</h3>
      </div>

      <div class="misi-grid">
        <div class="misi-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          Menyediakan sarana mudah dan aman bagi warga untuk menyampaikan pengaduan maupun aspirasi.
        </div>
        <div class="misi-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
          </svg>
          Mempercepat proses tindak lanjut oleh pemerintah desa melalui sistem yang terintegrasi.
        </div>
        <div class="misi-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Mendorong keterlibatan aktif masyarakat dalam pembangunan desa.
        </div>
        <div class="misi-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Menumbuhkan budaya transparansi dan akuntabilitas dalam tata kelola desa.
        </div>
      </div>
    </div>

  </main>

<?php include '../includes/footer.php'; ?>

  <script src="../../assets/js/script.js"></script>
</body>
</html>

