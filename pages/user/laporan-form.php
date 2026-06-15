<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$errors  = [];
$old     = [];

if (isset($_SESSION['laporan_errors'])) {
    $errors = $_SESSION['laporan_errors'];
    unset($_SESSION['laporan_errors']);
}
if (isset($_SESSION['laporan_old'])) {
    $old = $_SESSION['laporan_old'];
    unset($_SESSION['laporan_old']);
}

// Ambil kategori dari DB
$kategori_list = [];
$qk = $conn->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY id_kategori");
while ($row = $qk->fetch_assoc()) $kategori_list[] = $row;

$active_tab = $old['jenis'] ?? 'Pengaduan';
include '../includes/nav.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Buat Laporan – LaporKades</title>
  <link rel="stylesheet" href="../../assets/css/user.css"/>
</head>
<body class="tab-<?= strtolower($active_tab) ?>">

<main class="centered">
  <div class="form-wrapper animate">

    <div class="form-card">
      <h1>Sampaikan Laporan Anda</h1>

      <?php
        $globalErrors = [];
        foreach ($errors as $key => $message) {
            if (is_int($key) || $key === 'general') {
                $globalErrors[] = $message;
            }
        }
      ?>
      <?php if (!empty($globalErrors)): ?>
        <div class="alert-error">
          <?php foreach ($globalErrors as $message): ?>
            <p><?= htmlspecialchars($message) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Tab Toggle -->
      <div style="text-align:center">
        <div class="tab-toggle">
          <button type="button" class="tab-btn <?= $active_tab === 'Pengaduan' ? 'active' : '' ?>"
                  id="tab-pengaduan" onclick="setTab('Pengaduan')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            Pengaduan
          </button>
          <button type="button" class="tab-btn <?= $active_tab === 'Aspirasi' ? 'active' : '' ?>"
                  id="tab-aspirasi" onclick="setTab('Aspirasi')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Aspirasi
          </button>
        </div>
      </div>

      <form method="POST" action="../proses/user/proses-laporan.php" enctype="multipart/form-data">
        <input type="hidden" id="inputJenis" name="jenis" value="<?= htmlspecialchars($active_tab) ?>"/>

        <!-- Judul -->
        <div class="field">
          <label id="label-judul" for="judul">Ketik Judul Laporan Anda</label>
          <input type="text" id="judul" name="judul"
                 placeholder="Contoh: Jalan berlubang di RT 03"
                 value="<?= htmlspecialchars($old['judul'] ?? '') ?>"
                 class="<?= isset($errors['judul']) ? 'is-error' : '' ?>"/>
          <?php if (isset($errors['judul'])): ?><span class="error-msg"><?= $errors['judul'] ?></span><?php endif; ?>
        </div>

        <!-- Isi -->
        <div class="field">
          <label id="label-isi" for="isi">Ketik Isi Laporan Anda</label>
          <textarea id="isi" name="isi"
                    placeholder="Deskripsikan kejadian atau laporan secara detail..."
                    style="min-height:150px"
                    class="<?= isset($errors['isi']) ? 'is-error' : '' ?>"><?= htmlspecialchars($old['isi'] ?? '') ?></textarea>
          <?php if (isset($errors['isi'])): ?><span class="error-msg"><?= $errors['isi'] ?></span><?php endif; ?>
        </div>

        <!-- Khusus Pengaduan: Tanggal & Lokasi -->
        <div class="form-row pengaduan-only pengaduan-row" style="display:none">
          <div class="field">
            <label for="tgl">Pilih Tanggal Kejadian</label>
            <input type="date" id="tgl" name="tgl"
                   value="<?= htmlspecialchars($old['tgl'] ?? '') ?>"
                   class="<?= isset($errors['tgl']) ? 'is-error' : '' ?>"/>
            <?php if (isset($errors['tgl'])): ?><span class="error-msg"><?= $errors['tgl'] ?></span><?php endif; ?>
          </div>
          <div class="field">
            <label for="lokasi">Ketik Lokasi Kejadian</label>
            <div class="input-icon-wrap">
              <input type="text" id="lokasi" name="lokasi"
                     placeholder="Nama jalan, gedung, atau patokan"
                     value="<?= htmlspecialchars($old['lokasi'] ?? '') ?>"
                     class="<?= isset($errors['lokasi']) ? 'is-error' : '' ?>"/>
              <span class="input-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
              </span>
            </div>
            <?php if (isset($errors['lokasi'])): ?><span class="error-msg"><?= $errors['lokasi'] ?></span><?php endif; ?>
          </div>
        </div>

        <!-- Kategori -->
        <div class="field">
          <label for="kategori">Pilih Kategori Laporan</label>
          <div class="select-wrap">
            <select id="kategori" name="kategori"
                    class="<?= isset($errors['kategori']) ? 'is-error' : '' ?>">
              <option value="" disabled <?= empty($old['id_kategori']) ? 'selected' : '' ?>>Pilih Kategori...</option>
              <?php foreach ($kategori_list as $kat): ?>
                <option value="<?= $kat['id_kategori'] ?>"
                  <?= ($old['id_kategori'] ?? 0) == $kat['id_kategori'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($kat['nama_kategori']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (isset($errors['kategori'])): ?><span class="error-msg"><?= $errors['kategori'] ?></span><?php endif; ?>
        </div>

        <!-- Upload Lampiran — hanya Pengaduan -->
        <div class="field pengaduan-only" style="display:none">
          <label>Tambahkan Lampiran <span style="font-weight:400;color:var(--text-muted)">(opsional)</span></label>
          <div class="upload-area" id="uploadArea" onclick="document.getElementById('lampiran').click()">
            <div class="upload-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                <polyline points="13 2 13 9 20 9"/>
              </svg>
            </div>
            <p><a>Klik untuk unggah</a> atau seret dan lepas file di sini</p>
            <small>Maksimal 5MB. Format: JPG, PNG, PDF.</small>
            <input type="file" id="lampiran" name="lampiran"
                   accept=".jpg,.jpeg,.png,.pdf" style="display:none"
                   onchange="handleFile(this.files[0])"/>
          </div>

          <!-- Preview file yang dipilih -->
          <div id="filePreview" style="display:none;margin-top:10px;padding:10px 14px;background:#f8faf9;border:1px solid var(--border);border-radius:8px;display:none;align-items:center;gap:10px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--green-accent)" stroke-width="2">
              <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
              <polyline points="13 2 13 9 20 9"/>
            </svg>
            <span id="fileNameDisplay" style="font-size:0.85rem;font-weight:600;color:var(--text-dark);flex:1"></span>
            <span id="fileSizeDisplay" style="font-size:0.75rem;color:var(--text-muted)"></span>
            <button type="button" onclick="clearFile()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:18px;line-height:1">×</button>
          </div>

          <?php if (isset($errors['lampiran'])): ?>
            <span class="error-msg"><?= $errors['lampiran'] ?></span>
          <?php endif; ?>
        </div>

        <div class="form-footer">
          <button type="submit" class="btn btn-primary" style="padding:12px 36px">
            Lapor
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
          </button>
        </div>

      </form>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>

<script src="../../assets/js/script.js"></script>
</body>
</html>