<?php
// pages/admin/laporan.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

$tab   = $_GET['tab'] ?? 'belum';
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Query untuk tab "belum" (status = menunggu)
if ($tab === 'belum') {
    $total = $conn->query("SELECT COUNT(*) as c FROM laporan WHERE status_laporan = 'menunggu'")->fetch_assoc()['c'];
    $stmt  = $conn->prepare("
        SELECT l.*, u.nama_lengkap, k.nama_kategori
        FROM laporan l
        JOIN users u ON l.id_user = u.id_user
        JOIN kategori k ON l.id_kategori = k.id_kategori
        WHERE l.status_laporan = 'menunggu'
        ORDER BY l.tanggal_laporan DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param('ii', $limit, $offset);
} else {
    // Tab "sudah" (diproses)
    $total = $conn->query("SELECT COUNT(*) as c FROM laporan WHERE status_laporan = 'diproses'")->fetch_assoc()['c'];
    $stmt  = $conn->prepare("
        SELECT l.*, u.nama_lengkap, k.nama_kategori,
               a.nama_lengkap as nama_admin_proses
        FROM laporan l
        JOIN users u ON l.id_user = u.id_user
        JOIN kategori k ON l.id_kategori = k.id_kategori
        LEFT JOIN users a ON l.diproses_oleh = a.id_user
        WHERE l.status_laporan = 'diproses'
        ORDER BY l.diproses_pada DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$laporan = $stmt->get_result();
$total_pages = ceil($total / $limit);

$page_title = "Laporan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?> - LaporKades</title>
  <link rel="stylesheet" href="../../assets/css/admin.css" />
</head>
<body>

<div class="layout">
  <?php include __DIR__ . '/../includes/nav-admin.php'; ?>

  <main class="main">
    <h1 class="page-title">Manajemen Laporan</h1>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert-success" style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
      <?= $_SESSION['flash'] ?><?php unset($_SESSION['flash']); ?>
    </div>
    <?php endif; ?>

    <div class="section-header">
      <div class="section-label">Manajemen Laporan</div>
      <div class="section-desc">Daftar laporan aspirasi dan pengaduan warga.</div>
    </div>

    <!-- TABS -->
    <div class="tabs">
      <a href="?tab=belum" class="tab-btn <?= $tab==='belum'?'active':'' ?>">Belum Diproses</a>
      <a href="?tab=sudah" class="tab-btn <?= $tab==='sudah'?'active':'' ?>">Sedang Diproses</a>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Judul Laporan</th>
              <th>Jenis</th>
              <th>Kategori</th>
              <th>Status</th>
              <?php if ($tab === 'sudah'): ?>
              <th>Prioritas</th>
              <th>Diproses Oleh</th>
              <?php endif; ?>
              <th style="text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($laporan->num_rows === 0): ?>
            <tr>
              <td colspan="<?= $tab==='sudah'?8:7 ?>" style="text-align:center;color:var(--text-muted);padding:32px;">
                Tidak ada laporan
              </td>
            </tr>
            <?php else: $no = $offset + 1; while ($r = $laporan->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                <div class="report-title"><?= htmlspecialchars($r['judul']) ?></div>
                <div class="report-by">
                  <?= $tab==='belum' ? 'Oleh: '.htmlspecialchars($r['nama_lengkap']) : 'Diterima: '.date('d M Y', strtotime($r['diproses_pada'] ?? $r['tanggal_laporan'])) ?>
                </div>
              </td>
              <td>
                <span class="badge <?= $r['jenis_laporan']==='Pengaduan'?'badge-pengaduan':'badge-aspirasi' ?>">
                  <?= strtoupper($r['jenis_laporan']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
              <td>
                <span class="badge badge-<?= $r['status_laporan'] ?>">
                  <?= ucfirst($r['status_laporan']) ?>
                </span>
              </td>
              <?php if ($tab === 'sudah'): ?>
              <td>
                <?php if ($r['prioritas']): ?>
                <span class="badge badge-<?= strtolower($r['prioritas']) ?>"><?= $r['prioritas'] ?></span>
                <?php else: ?>
                <span style="color:var(--text-label);font-size:12px;">-</span>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($r['nama_admin_proses'] ?? '-') ?></span>
              </td>
              <?php endif; ?>
              <td style="text-align:center;">
                <?php $link = $tab==='belum' ? "detail-laporan-belum.php?id={$r['id_laporan']}" : "detail-laporan-sudah.php?id={$r['id_laporan']}"; ?>
                <a href="<?= $link ?>" class="action-icon" title="Lihat Detail">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
              </td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <span class="pagination-info">
          Menampilkan <?= min($offset+1, $total) ?>–<?= min($offset+$limit, $total) ?> dari <?= $total ?> laporan
        </span>
        <div class="pagination-pages">
          <?php if ($page > 1): ?>
          <button onclick="goPage(<?= $page-1 ?>)">&lsaquo;</button>
          <?php endif; ?>
          <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
          <button class="<?= $i===$page?'active':'' ?>" onclick="goPage(<?= $i ?>)"><?= $i ?></button>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
          <button onclick="goPage(<?= $page+1 ?>)">&rsaquo;</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="../../assets/js/admin.js"></script>
</body>
</html>