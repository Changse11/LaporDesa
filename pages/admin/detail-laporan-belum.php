<?php
// pages/admin/detail-laporan-belum.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

$id    = (int)($_GET['id'] ?? 0);
$admin = $_SESSION['user'];

if (!$id) { header('Location: laporan.php'); exit; }

// Ambil data laporan
$stmt = $conn->prepare("
    SELECT l.*, u.nama_lengkap, u.username, k.nama_kategori
    FROM laporan l
    JOIN users u ON l.id_user = u.id_user
    JOIN kategori k ON l.id_kategori = k.id_kategori
    WHERE l.id_laporan = ? AND l.status_laporan = 'menunggu'
");
$stmt->bind_param('i', $id);
$stmt->execute();
$lap = $stmt->get_result()->fetch_assoc();
if (!$lap) { header('Location: laporan.php'); exit; }

// Handle aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi      = $_POST['aksi'] ?? '';
    $komentar  = trim($_POST['komentar'] ?? '');
    $prioritas = $_POST['prioritas'] ?? null;

    if ($aksi === 'terima') {
        // Update status laporan
        $stmt = $conn->prepare("
            UPDATE laporan SET
              status_laporan = 'diproses',
              prioritas = ?,
              diproses_oleh = ?,
              diproses_pada = NOW(),
              updated_at = NOW()
            WHERE id_laporan = ?
        ");
        $stmt->bind_param('sii', $prioritas, $admin['id_user'], $id);
        $stmt->execute();

        // Simpan komentar jika ada
        if ($komentar !== '') {
            $stmt2 = $conn->prepare("INSERT INTO komentar (id_laporan, id_user, isi_komentar, tanggal) VALUES (?, ?, ?, NOW())");
            $stmt2->bind_param('iis', $id, $admin['id_user'], $komentar);
            $stmt2->execute();
        }

        $_SESSION['flash'] = "Laporan berhasil diterima dan diproses.";
        header('Location: laporan.php?tab=sudah');
        exit;

    } elseif ($aksi === 'tolak') {
        $stmt = $conn->prepare("
            UPDATE laporan SET
              status_laporan = 'ditolak',
              diproses_oleh = ?,
              diproses_pada = NOW(),
              updated_at = NOW()
            WHERE id_laporan = ?
        ");
        $stmt->bind_param('ii', $admin['id_user'], $id);
        $stmt->execute();

        if ($komentar !== '') {
            $stmt2 = $conn->prepare("INSERT INTO komentar (id_laporan, id_user, isi_komentar, tanggal) VALUES (?, ?, ?, NOW())");
            $stmt2->bind_param('iis', $id, $admin['id_user'], $komentar);
            $stmt2->execute();
        }

        $_SESSION['flash'] = "Laporan telah ditolak.";
        header('Location: laporan.php');
        exit;
    }
}

$is_pengaduan = $lap['jenis_laporan'] === 'Pengaduan';
$page_title   = "Detail Laporan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?> - LaporDesa</title>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
</head>
<body>

<div class="layout">
  <?php include __DIR__ . '/../includes/nav-admin.php'; ?>

  <main class="main">
    <h1 class="page-title">Manajemen Laporan</h1>

    <div class="section-header">
      <div class="section-label">Detail Laporan — Belum Diproses</div>
      <div class="section-desc">Review informasi laporan sebelum diambil tindakan.</div>
    </div>

    <form method="POST" id="formLaporan" action="../proses/admin/proses-laporan.php?id=<?= $id ?>">
    <div class="detail-card">
      <div class="top-accent"></div>
      <div class="form-grid">

        <div class="form-group">
          <label>Nomor Laporan</label>
          <input class="form-control" readonly value="<?= htmlspecialchars($lap['nomor_laporan']) ?>" />
        </div>
        <div class="form-group">
          <label>Jenis Laporan</label>
          <input class="form-control" readonly value="<?= htmlspecialchars($lap['jenis_laporan']) ?>" />
        </div>
        <div class="form-group">
          <label>Judul Laporan</label>
          <input class="form-control" readonly value="<?= htmlspecialchars($lap['judul']) ?>" />
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <input class="form-control" readonly value="<?= htmlspecialchars($lap['nama_kategori']) ?>" />
        </div>
        <div class="form-group">
          <label>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Username Pelapor
          </label>
          <input class="form-control" readonly value="<?= htmlspecialchars($lap['username']) ?> (<?= htmlspecialchars($lap['nama_lengkap']) ?>)" />
        </div>
        <div class="form-group">
          <label>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Tanggal Laporan
          </label>
          <input class="form-control" readonly value="<?= date('d F Y H:i', strtotime($lap['tanggal_laporan'])) ?>" />
        </div>

        <?php if ($is_pengaduan): ?>
        <div class="form-group">
          <label>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
            Tanggal Kejadian
          </label>
          <input class="form-control" readonly value="<?= $lap['tanggal_kejadian'] ? date('d F Y', strtotime($lap['tanggal_kejadian'])) : '-' ?>" />
        </div>
        <div class="form-group">
          <label>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Lokasi Kejadian
          </label>
          <input class="form-control" readonly value="<?= htmlspecialchars($lap['lokasi_kejadian'] ?? '-') ?>" />
        </div>
        <?php endif; ?>

        <div class="form-group full">
          <label>Isi Laporan</label>
          <textarea class="form-control" readonly rows="5"><?= htmlspecialchars($lap['isi_laporan']) ?></textarea>
        </div>

        <?php if ($is_pengaduan && $lap['file_path']): ?>
        <div class="form-group full">
          <label>Lampiran Bukti</label>
          <div class="photo-box" onclick="window.open('../../<?= htmlspecialchars($lap['file_path']) ?>', '_blank')" style="cursor:pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span>Klik untuk lihat foto lampiran</span>
          </div>
        </div>
        <?php elseif ($is_pengaduan && !$lap['file_path']): ?>
        <div class="form-group full">
          <label>Lampiran Bukti</label>
          <div class="photo-box" style="cursor:default;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span style="color:var(--text-label);">Tidak ada lampiran</span>
          </div>
        </div>
        <?php endif; ?>

        <!-- === BAGIAN TINDAK LANJUT ADMIN === -->
        <div class="form-group" id="prioritasGroup">
          <label>Prioritas Penanganan <span style="color:var(--text-label);font-weight:400;text-transform:none;letter-spacing:0;font-size:10px;">(wajib jika diterima)</span></label>
          <select class="form-control" name="prioritas" id="prioritasSelect">
            <option value="">-- Pilih Prioritas --</option>
            <option value="Rendah">Rendah</option>
            <option value="Sedang">Sedang</option>
            <option value="Tinggi">Tinggi</option>
          </select>
        </div>

        <div class="form-group <?= !$is_pengaduan ? 'full' : '' ?>">
          <label>Komentar Admin <span style="color:var(--text-label);">(opsional)</span></label>
          <textarea class="form-control" name="komentar" rows="4" placeholder="Tambahkan catatan atau alasan tindak lanjut..."></textarea>
        </div>

      </div>

      <div class="form-actions">
        <a href="laporan.php" class="btn btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali
        </a>
        <button type="button" class="btn btn-danger" onclick="aksiLaporan('tolak')">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Tolak Laporan
        </button>
        <button type="button" class="btn btn-primary" onclick="aksiLaporan('terima')">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Terima &amp; Proses
        </button>
      </div>
    </div>
    <input type="hidden" name="aksi" id="inputAksi" value="">
    </form>
  </main>
</div>

<script src="../../assets/js/admin.js"></script>
</body>
</html>