<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: /RPL/pages/auth/masuk.php'); exit;
}

$id_user = (int)$_SESSION['id_user'];
$nomor   = $_GET['nomor'] ?? '';

if (empty($nomor)) { header('Location: status-laporan.php'); exit; }

// Ambil laporan milik user ini
$stmt = $conn->prepare("
    SELECT l.*, k.nama_kategori,
           acc.nama_lengkap  AS nama_admin_acc,
           done.nama_lengkap AS nama_admin_done
    FROM laporan l
    JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users acc  ON l.diproses_oleh = acc.id_user
    LEFT JOIN users done ON l.selesai_oleh  = done.id_user
    WHERE l.nomor_laporan = ? AND l.id_user = ?
");
$stmt->bind_param('si', $nomor, $id_user);
$stmt->execute();
$lap = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lap) { header('Location: status-laporan.php'); exit; }

// Ambil komentar admin
$komentar_list = [];
$qk = $conn->prepare("
    SELECT km.isi_komentar, km.tanggal, u.nama_lengkap, u.role
    FROM komentar km
    JOIN users u ON km.id_user = u.id_user
    WHERE km.id_laporan = ?
    ORDER BY km.tanggal ASC
");
$qk->bind_param('i', $lap['id_laporan']);
$qk->execute();
$rk = $qk->get_result();
while ($row = $rk->fetch_assoc()) $komentar_list[] = $row;
$qk->close();

$is_aspirasi = ($lap['jenis_laporan'] === 'Aspirasi');
$status      = $lap['status_laporan'];

$status_map = [
    'menunggu' => ['Menunggu', 'status-menunggu', '🕐'],
    'diproses' => ['Diproses', 'status-proses',   '🔄'],
    'selesai'  => ['Selesai',  'status-selesai',  '✅'],
    'ditolak'  => ['Ditolak',  'status-ditolak',  '❌'],
];
[$status_label, $status_cls, $status_emoji] = $status_map[$status] ?? [ucfirst($status), 'status-menunggu', '•'];

include '../includes/nav.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Detail Laporan – LaporKades</title>
  <link rel="stylesheet" href="../../assets/css/user.css"/>
</head>
<body>

<main>
<div class="detail-wrapper animate">

  <!-- Back -->
  <a href="status-laporan.php" style="display:inline-flex;align-items:center;gap:6px;font-size:0.85rem;color:var(--text-muted);text-decoration:none;margin-bottom:16px;font-weight:600">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Daftar Laporan
  </a>

  <!-- Header Card -->
  <div class="detail-header-card">
    <div class="detail-header-left">
      <div class="nomor"><?= htmlspecialchars($lap['nomor_laporan']) ?></div>
      <h2><?= htmlspecialchars($lap['judul']) ?></h2>
      <div class="detail-meta-row">
        <span class="status-dot <?= $status_cls ?>"><?= $status_emoji ?> <?= $status_label ?></span>
        <span class="badge <?= $lap['jenis_laporan'] === 'Pengaduan' ? 'badge-yellow' : 'badge-blue' ?>">
          <?= htmlspecialchars($lap['jenis_laporan']) ?>
        </span>
        <span style="font-size:0.8rem;color:var(--text-muted)"><?= htmlspecialchars($lap['nama_kategori']) ?></span>
        <?php if ($status === 'diproses' && $lap['prioritas']): ?>
          <?php $pc = ['Tinggi'=>'#ef4444','Sedang'=>'#d97706','Rendah'=>'#16a34a']; ?>
          <span style="font-size:0.78rem;font-weight:700;color:<?= $pc[$lap['prioritas']] ?? '#666' ?>">
            Prioritas: <?= $lap['prioritas'] ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
    <div class="detail-header-right">
      Dikirim<br>
      <strong style="color:var(--text-dark)"><?= date('d M Y, H:i', strtotime($lap['tanggal_laporan'])) ?></strong>
    </div>
  </div>

  <!-- Detail Isi -->
  <div class="info-card">
    <h3>Detail <?= $is_aspirasi ? 'Aspirasi' : 'Laporan' ?></h3>
    <div class="info-grid">

      <div class="info-item" style="grid-column:1/-1">
        <span class="label">Isi <?= $is_aspirasi ? 'Aspirasi' : 'Laporan' ?></span>
        <div class="isi-box"><?= htmlspecialchars($lap['isi_laporan']) ?></div>
      </div>

      <?php if (!$is_aspirasi && $lap['tanggal_kejadian']): ?>
      <div class="info-item">
        <span class="label">Tanggal Kejadian</span>
        <span class="value"><?= date('d M Y', strtotime($lap['tanggal_kejadian'])) ?></span>
      </div>
      <?php endif; ?>

      <?php if (!$is_aspirasi && $lap['lokasi_kejadian']): ?>
      <div class="info-item">
        <span class="label">Lokasi Kejadian</span>
        <span class="value"><?= htmlspecialchars($lap['lokasi_kejadian']) ?></span>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Lampiran — hanya Pengaduan -->
  <?php if (!$is_aspirasi): ?>
  <div class="info-card">
    <h3>Lampiran Bukti</h3>
    <?php if ($lap['file_path']): ?>
      <?php $ext = strtolower(pathinfo($lap['file_path'], PATHINFO_EXTENSION)); $url = '/RPL/' . $lap['file_path']; ?>
      <?php if (in_array($ext, ['jpg','jpeg','png'])): ?>
        <img src="<?= htmlspecialchars($url) ?>" alt="Lampiran" class="lampiran-img"/>
      <?php else: ?>
        <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="lampiran-pdf">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
          Buka File PDF
        </a>
      <?php endif; ?>
    <?php else: ?>
      <p style="color:var(--text-muted);font-size:0.875rem">Tidak ada lampiran yang diunggah.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Timeline -->
  <div class="tracking-card">
    <h3>Riwayat Status</h3>
    <div class="timeline">

      <div class="tl-item">
        <div class="tl-dot dot-sent">📨</div>
        <div class="tl-body">
          <div class="tl-title">Laporan Dikirim</div>
          <div class="tl-time"><?= date('d M Y, H:i', strtotime($lap['tanggal_laporan'])) ?></div>
          <div class="tl-by">Laporan berhasil masuk ke sistem</div>
        </div>
      </div>

      <?php if (in_array($status, ['diproses','selesai','ditolak'])): ?>
      <div class="tl-item">
        <div class="tl-dot <?= $status === 'ditolak' ? 'dot-rejected' : 'dot-process' ?>">
          <?= $status === 'ditolak' ? '❌' : '🔄' ?>
        </div>
        <div class="tl-body">
          <div class="tl-title"><?= $status === 'ditolak' ? 'Laporan Ditolak' : 'Laporan Diterima & Diproses' ?></div>
          <?php if ($lap['diproses_pada']): ?>
            <div class="tl-time"><?= date('d M Y, H:i', strtotime($lap['diproses_pada'])) ?></div>
          <?php endif; ?>
          <?php if ($lap['nama_admin_acc']): ?>
            <div class="tl-by">Oleh: <strong><?= htmlspecialchars($lap['nama_admin_acc']) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($status === 'selesai'): ?>
      <div class="tl-item">
        <div class="tl-dot dot-done">✅</div>
        <div class="tl-body">
          <div class="tl-title">Laporan Diselesaikan</div>
          <?php if ($lap['selesai_pada']): ?>
            <div class="tl-time"><?= date('d M Y, H:i', strtotime($lap['selesai_pada'])) ?></div>
          <?php endif; ?>
          <?php if ($lap['nama_admin_done']): ?>
            <div class="tl-by">Oleh: <strong><?= htmlspecialchars($lap['nama_admin_done']) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Komentar Admin -->
  <div class="komentar-card">
    <h3>Komentar dari Admin</h3>
    <?php if (empty($komentar_list)): ?>
      <div class="komentar-empty">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;opacity:.3">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Belum ada komentar dari admin.
      </div>
    <?php else: ?>
      <?php foreach ($komentar_list as $km): ?>
      <div class="komentar-item">
        <div class="komentar-avatar <?= $km['role'] === 'admin' ? 'admin-avatar' : '' ?>">
          <?= $km['role'] === 'admin' ? '👤' : strtoupper(substr($km['nama_lengkap'], 0, 1)) ?>
        </div>
        <div style="flex:1">
          <div class="komentar-meta">
            <strong><?= htmlspecialchars($km['nama_lengkap']) ?></strong>
            <?php if ($km['role'] === 'admin'): ?>
              <span style="background:#1a3a2a;color:white;border-radius:4px;padding:1px 6px;font-size:10px;font-weight:700;margin-left:4px">Admin</span>
            <?php endif; ?>
            · <?= date('d M Y, H:i', strtotime($km['tanggal'])) ?>
          </div>
          <div class="komentar-isi"><?= nl2br(htmlspecialchars($km['isi_komentar'])) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>
</main>

<?php include '../includes/footer.php'; ?>
<script src="../../assets/js/script.js"></script>
</body>
</html>