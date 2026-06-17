<?php
// pages/admin/detail-laporan-sudah.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

$id    = (int)($_GET['id'] ?? 0);
$admin = $_SESSION['user'];

if (!$id) { header('Location: laporan.php?tab=sudah'); exit; }

$stmt = $conn->prepare("
    SELECT l.*, u.nama_lengkap, u.username, k.nama_kategori,
           a.nama_lengkap as nama_admin_proses,
           s.nama_lengkap as nama_admin_selesai
    FROM laporan l
    JOIN users u ON l.id_user = u.id_user
    JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users a ON l.diproses_oleh = a.id_user
    LEFT JOIN users s ON l.selesai_oleh = s.id_user
    WHERE l.id_laporan = ? AND l.status_laporan = 'diproses'
");
$stmt->bind_param('i', $id);
$stmt->execute();
$lap = $stmt->get_result()->fetch_assoc();
if (!$lap) { header('Location: laporan.php?tab=sudah'); exit; }

// Ambil komentar-komentar sebelumnya
$kom_stmt = $conn->prepare("
    SELECT km.*, u.nama_lengkap, u.role
    FROM komentar km
    JOIN users u ON km.id_user = u.id_user
    WHERE km.id_laporan = ?
    ORDER BY km.tanggal ASC
");
$kom_stmt->bind_param('i', $id);
$kom_stmt->execute();
$komentar_list = $kom_stmt->get_result();

// Handle selesai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'selesai') {
    $komentar_baru = trim($_POST['komentar'] ?? '');

    $stmt2 = $conn->prepare("
        UPDATE laporan SET
          status_laporan = 'selesai',
          selesai_oleh = ?,
          selesai_pada = NOW(),
          updated_at = NOW()
        WHERE id_laporan = ?
    ");
    $stmt2->bind_param('ii', $admin['id_user'], $id);
    $stmt2->execute();

    if ($komentar_baru !== '') {
        $stmt3 = $conn->prepare("INSERT INTO komentar (id_laporan, id_user, isi_komentar, tanggal) VALUES (?, ?, ?, NOW())");
        $stmt3->bind_param('iis', $id, $admin['id_user'], $komentar_baru);
        $stmt3->execute();
    }

    $_SESSION['flash'] = "Laporan berhasil ditandai sebagai selesai.";
    header('Location: histori.php');
    exit;
}

$is_pengaduan = $lap['jenis_laporan'] === 'Pengaduan';
$page_title   = "Detail Laporan (Diproses)";
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
      <div class="section-label">Detail Laporan — Sedang Diproses</div>
      <div class="section-desc">Informasi lengkap laporan yang sedang ditindaklanjuti.</div>
    </div>

    <form method="POST" id="formSelesai" action="../proses/admin/proses-laporan.php?id=<?= $id ?>">
    <div class="detail-card">
      <div class="top-accent"></div>

      <!-- Info tracking admin -->
      <div style="background:var(--primary-light);border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;gap:24px;flex-wrap:wrap;">
        <div style="font-size:12px;">
          <span style="color:var(--text-label);display:block;margin-bottom:2px;">Diterima oleh</span>
          <strong style="color:var(--primary);"><?= htmlspecialchars($lap['nama_admin_proses'] ?? '-') ?></strong>
          <?php if ($lap['diproses_pada']): ?>
          <span style="color:var(--text-muted);margin-left:6px;"><?= date('d M Y H:i', strtotime($lap['diproses_pada'])) ?></span>
          <?php endif; ?>
        </div>
        <div style="font-size:12px;">
          <span style="color:var(--text-label);display:block;margin-bottom:2px;">Prioritas</span>
          <span class="badge badge-<?= strtolower($lap['prioritas']) ?>"><?= $lap['prioritas'] ?></span>
        </div>
        <div style="font-size:12px;">
          <span style="color:var(--text-label);display:block;margin-bottom:2px;">Status</span>
          <span class="badge badge-diproses">Diproses</span>
        </div>
      </div>

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
          <label>Pelapor</label>
          <input class="form-control" readonly value="<?= htmlspecialchars($lap['username']) ?> (<?= htmlspecialchars($lap['nama_lengkap']) ?>)" />
        </div>
        <div class="form-group">
          <label>Tanggal Laporan</label>
          <input class="form-control" readonly value="<?= date('d F Y H:i', strtotime($lap['tanggal_laporan'])) ?>" />
        </div>

        <?php if ($is_pengaduan): ?>
        <div class="form-group">
          <label>Tanggal Kejadian</label>
          <input class="form-control" readonly value="<?= $lap['tanggal_kejadian'] ? date('d F Y', strtotime($lap['tanggal_kejadian'])) : '-' ?>" />
        </div>
        <div class="form-group">
          <label>Lokasi Kejadian</label>
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
        <?php endif; ?>
      </div>

      <!-- Riwayat Komentar -->
      <?php if ($komentar_list->num_rows > 0): ?>
      <div style="margin-top:20px;">
        <div class="section-label" style="margin-bottom:10px;">Riwayat Komentar</div>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <?php while ($km = $komentar_list->fetch_assoc()): ?>
          <div style="background:#f8faf9;border:1px solid var(--border);border-radius:8px;padding:10px 14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
              <strong style="font-size:12px;color:var(--primary);">
                <?= htmlspecialchars($km['nama_lengkap']) ?>
                <?= $km['role']==='admin' ? '<span style="background:var(--primary);color:white;font-size:10px;padding:1px 6px;border-radius:10px;margin-left:4px;">Admin</span>' : '' ?>
              </strong>
              <span style="font-size:11px;color:var(--text-label);"><?= date('d M Y H:i', strtotime($km['tanggal'])) ?></span>
            </div>
            <p style="font-size:13px;color:var(--text-main);"><?= nl2br(htmlspecialchars($km['isi_komentar'])) ?></p>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Catatan selesai -->
      <div style="margin-top:20px;">
        <div class="form-group">
          <label>Catatan Penyelesaian <span style="color:var(--text-label);">(opsional)</span></label>
          <textarea class="form-control" name="komentar" rows="3" placeholder="Tambahkan catatan penyelesaian laporan..."></textarea>
        </div>
      </div>

      <div class="form-actions">
        <a href="laporan.php?tab=sudah" class="btn btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali
        </a>
        <button type="submit" class="btn btn-primary" onclick="return confirm('Tandai laporan ini sebagai selesai?')">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Tandai Selesai
        </button>
      </div>
    </div>
    <input type="hidden" name="aksi" value="selesai">
    </form>
  </main>
</div>

</body>
</html>