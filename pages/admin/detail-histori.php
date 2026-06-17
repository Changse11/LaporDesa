<?php
// pages/admin/detail-histori.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: histori.php'); exit; }

$stmt = $conn->prepare("
    SELECT l.*, u.nama_lengkap, u.username, k.nama_kategori,
           a.nama_lengkap as nama_admin_proses,
           s.nama_lengkap as nama_admin_selesai
    FROM laporan l
    JOIN users u ON l.id_user = u.id_user
    JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users a ON l.diproses_oleh = a.id_user
    LEFT JOIN users s ON l.selesai_oleh = s.id_user
    WHERE l.id_laporan = ? AND l.status_laporan IN ('selesai', 'ditolak')
");
$stmt->bind_param('i', $id);
$stmt->execute();
$lap = $stmt->get_result()->fetch_assoc();
if (!$lap) { header('Location: histori.php'); exit; }

// Komentar
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

$is_pengaduan = $lap['jenis_laporan'] === 'Pengaduan';
$is_selesai   = $lap['status_laporan'] === 'selesai';
$page_title   = "Detail Histori";
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
    <h1 class="page-title">Histori Laporan</h1>

    <div class="section-header">
      <div class="section-label">Detail Histori</div>
      <div class="section-desc">Laporan yang telah <?= $is_selesai ? 'diselesaikan' : 'ditolak' ?>.</div>
    </div>

    <div class="detail-card">
      <div class="top-accent" style="background:<?= $is_selesai ? 'linear-gradient(90deg,#15803d,#4caf78)' : 'linear-gradient(90deg,#b91c1c,#ef4444)' ?>;"></div>

      <!-- Timeline status -->
      <div style="background:#f8faf9;border:1px solid var(--border);border-radius:8px;padding:14px 18px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:24px;">
        <div style="font-size:12px;">
          <span style="color:var(--text-label);display:block;margin-bottom:3px;">Status Akhir</span>
          <?php if ($is_selesai): ?>
          <span class="badge badge-selesai">Selesai</span>
          <?php else: ?>
          <span class="badge" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;">Ditolak</span>
          <?php endif; ?>
        </div>
        <?php if ($lap['diproses_oleh'] && $lap['diproses_pada']): ?>
        <div style="font-size:12px;">
          <span style="color:var(--text-label);display:block;margin-bottom:3px;">Diproses oleh</span>
          <strong style="color:var(--primary);"><?= htmlspecialchars($lap['nama_admin_proses']) ?></strong>
          <span style="color:var(--text-muted);margin-left:4px;font-size:11px;"><?= date('d M Y H:i', strtotime($lap['diproses_pada'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($is_selesai && $lap['selesai_oleh'] && $lap['selesai_pada']): ?>
        <div style="font-size:12px;">
          <span style="color:var(--text-label);display:block;margin-bottom:3px;">Diselesaikan oleh</span>
          <strong style="color:var(--primary);"><?= htmlspecialchars($lap['nama_admin_selesai']) ?></strong>
          <span style="color:var(--text-muted);margin-left:4px;font-size:11px;"><?= date('d M Y H:i', strtotime($lap['selesai_pada'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($lap['prioritas']): ?>
        <div style="font-size:12px;">
          <span style="color:var(--text-label);display:block;margin-bottom:3px;">Prioritas</span>
          <span class="badge badge-<?= strtolower($lap['prioritas']) ?>"><?= $lap['prioritas'] ?></span>
        </div>
        <?php endif; ?>
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
                <?php if ($km['role']==='admin'): ?>
                <span style="background:var(--primary);color:white;font-size:10px;padding:1px 6px;border-radius:10px;margin-left:4px;">Admin</span>
                <?php endif; ?>
              </strong>
              <span style="font-size:11px;color:var(--text-label);"><?= date('d M Y H:i', strtotime($km['tanggal'])) ?></span>
            </div>
            <p style="font-size:13px;color:var(--text-main);"><?= nl2br(htmlspecialchars($km['isi_komentar'])) ?></p>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="form-actions">
        <a href="histori.php" class="btn btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali ke Histori
        </a>
      </div>
    </div>
  </main>
</div>

</body>
</html>