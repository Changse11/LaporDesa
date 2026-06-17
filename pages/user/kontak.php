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
  <title>Kontak – LaporDesa</title>
  <link rel="stylesheet" href="../../assets/css/user.css"/>
</head>
<body>

  <main>
    <div class="kontak-wrapper">

      <div class="kontak-header animate">
        <h1>Kontak Kami</h1>
        <p>Kami siap mendengar setiap pengaduan, aspirasi, maupun saran dari masyarakat Desa Kutapohaci. Jika ada pertanyaan silahkan hubungi kami melalui kontak berikut:</p>
      </div>

      <div class="kontak-grid animate delay-1">

        <!-- Alamat -->
        <div class="kontak-card">
          <h3>📍 Alamat Kantor</h3>
          <p>
            <strong>Balai Desa Kutapohaci</strong><br>
            Jl. Raya Kutapohaci No. XX<br>
            Desa Kutapohaci, Kecamatan Ciampel<br>
            Kabupaten Karawang, Jawa Barat – 41361
          </p>
        </div>

        <!-- Telepon & Email -->
        <div class="kontak-card">
          <div class="label-kecil">Nomor Telepon</div>
          <div class="telp-number">(0267) 1234567</div>
          <br>
          <div class="label-kecil">Email</div>
          <a href="mailto:sekdes.kutapohaci@karawangkab.go.id">sekdes.kutapohaci@karawangkab.go.id</a>
        </div>

        <!-- Jam Layanan -->
        <div class="kontak-card">
          <h3>🕐 Jam Layanan</h3>
          <p>
            <strong>Senin – Jumat:</strong> 08.00 – 15.00 WIB<br>
            <strong>Sabtu:</strong> 08.00 – 12.00 WIB<br>
            <span class="minggu-libur">Minggu: Libur</span>
          </p>
        </div>

        <!-- Media Sosial -->
        <div class="kontak-card">
          <h3>🌐 Media Sosial</h3>
          <div class="sosmed-row">
            <div><span>Facebook:</span><span>Desa Kutapohaci Karawang</span></div>
            <div><span>Instagram:</span><span>@desakutapohaci</span></div>
            <div><span>YouTube:</span><span>Desa Kutapohaci Karawang</span></div>
          </div>
        </div>

      </div>

      <!-- Peta -->
      <div class="peta-card animate delay-2">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.5!2d107.3582735!3d-6.3960886!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69745e762d091f%3A0x79a9502c27463029!2sKutapohaci%2C+Kec.+Ciampel%2C+Karawang%2C+Jawa+Barat!5e0!3m2!1sid!2sid!4v1"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

    </div>
  </main>

  <?php include '../includes/footer.php'; ?>

  <script src="../../assets/js/script.js"></script>
</body>
</html>