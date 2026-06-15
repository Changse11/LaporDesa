<?php include 'pages/lib/koneksi.php'; 
include 'pages/includes/nav.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home – LaporKades</title>
  <link rel="stylesheet" href="/RPL/assets/css/user.css"/>
  
</head>
<body>
  <main>
    <section class="hero animate">
      <div class="hero-text">
        <div class="hero-badge"><span>🏛️</span> Portal Resmi Desa</div>
        <h1>Suarakan Aspirasi Anda untuk Desa yang Lebih Baik</h1>
        <p>LaporKades adalah platform digital transparan untuk menyampaikan keluhan, saran, dan aspirasi langsung kepada pemerintah desa. Kami memastikan setiap suara didengar demi kemajuan bersama.</p>
        <div class="hero-actions">
          <a href="/RPL/pages/user/laporan-form.php" class="btn btn-primary">Mulai Lapor →</a>
          <a href="/RPL/pages/user/tentang.php" class="btn btn-outline">Pelajari Selengkapnya</a>
        </div>
      </div>
      <div class="hero-img animate delay-1">
        <img src="/RPL/assets/img/desa.jpg" alt="Foto Desa Kutapohaci"/>
      </div>
    </section>

    <section class="how-section animate delay-2">
      <div class="section-title">
        <h2>Bagaimana LaporKades Bekerja?</h2>
        <p>Proses pelaporan dirancang agar mudah, transparan, dan dapat dilacak oleh seluruh warga desa tanpa birokrasi yang rumit.</p>
      </div>
      <div class="steps-grid">
        <div class="step-card">
          <div class="step-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
          </div>
          <h3>1. Tulis Laporan</h3>
          <p>Sampaikan aspirasi atau keluhan Anda melalui formulir yang ringkas. Tambahkan foto atau bukti pendukung untuk memperjelas konteks.</p>
        </div>
        <div class="step-card">
          <div class="step-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              <line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
          </div>
          <h3>2. Verifikasi &amp; Proses</h3>
          <p>Admin desa akan memverifikasi laporan Anda. Laporan yang valid akan langsung diteruskan ke perangkat desa yang berwenang.</p>
        </div>
        <div class="step-card">
          <div class="step-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </div>
          <h3>3. Pantau Status</h3>
          <p>Lacak perkembangan laporan Anda secara real-time. Anda akan menerima notifikasi setiap kali ada pembaruan status penyelesaian.</p>
        </div>
      </div>
    </section>

    <div class="cta-banner animate delay-3">
      <div>
        <h2>Siap Berkontribusi?</h2>
        <p>Setiap aspirasi Anda sangat berarti bagi pembangunan desa. Mari bersama-sama wujudkan lingkungan yang lebih baik.</p>
      </div>
      <button class="btn-cta" onclick="window.location.href='/RPL/pages/user/laporan-pengaduan.php'">Buat Laporan Sekarang</button>
    </div>
  </main>

  <?php include 'pages/includes/footer.php'; ?>

</body>
</html>

