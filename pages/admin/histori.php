<?php
// pages/admin/histori.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$where  = "WHERE l.status_laporan IN ('selesai', 'ditolak')";
$params = [];
$types  = '';

if ($search !== '') {
    $where  .= " AND (l.judul LIKE ? OR k.nama_kategori LIKE ? OR u.nama_lengkap LIKE ?)";
    $like    = "%$search%";
    $params  = [$like, $like, $like];
    $types   = 'sss';
}

// Total
$count_sql = "SELECT COUNT(*) as c FROM laporan l JOIN users u ON l.id_user=u.id_user JOIN kategori k ON l.id_kategori=k.id_kategori $where";
if ($params) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['c'];
} else {
    $total = $conn->query($count_sql)->fetch_assoc()['c'];
}
$total_pages = ceil($total / $limit);

// Data
$sql = "
    SELECT l.*, u.nama_lengkap, k.nama_kategori,
           a.nama_lengkap as nama_admin_proses,
           s.nama_lengkap as nama_admin_selesai
    FROM laporan l
    JOIN users u ON l.id_user = u.id_user
    JOIN kategori k ON l.id_kategori = k.id_kategori
    LEFT JOIN users a ON l.diproses_oleh = a.id_user
    LEFT JOIN users s ON l.selesai_oleh = s.id_user
    $where
    ORDER BY COALESCE(l.selesai_pada, l.diproses_pada) DESC
    LIMIT ? OFFSET ?
";
if ($params) {
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$laporan = $stmt->get_result();

$page_title = "Histori";
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

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert-success" style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
      <?= $_SESSION['flash'] ?><?php unset($_SESSION['flash']); ?>
    </div>
    <?php endif; ?>

    <div class="section-header">
      <div class="section-label">Histori Laporan</div>
      <div class="section-desc">Laporan yang telah diselesaikan atau ditolak.</div>
    </div>

    <!-- Search -->
    <form method="GET" action="">
    <div class="history-search" style="margin-bottom:20px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" placeholder="Cari judul, kategori, atau pelapor..." value="<?= htmlspecialchars($search) ?>" />
    </div>
    </form>

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
              <th>Admin Pemroses</th>
              <th>Admin Penyelesai</th>
              <th style="text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($laporan->num_rows === 0): ?>
            <tr>
              <td colspan="8" style="text-align:center;color:var(--text-muted);padding:32px;">
                <?= $search ? "Tidak ada hasil untuk \"$search\"" : "Belum ada histori laporan" ?>
              </td>
            </tr>
            <?php else: $no = $offset + 1; while ($r = $laporan->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                <div class="report-title"><?= htmlspecialchars($r['judul']) ?></div>
                <div class="report-by">
                  <?php
                    $tgl_ref = $r['status_laporan'] === 'selesai' ? $r['selesai_pada'] : $r['diproses_pada'];
                    echo $tgl_ref ? date('d M Y H:i', strtotime($tgl_ref)) : '';
                  ?>
                </div>
              </td>
              <td>
                <span class="badge <?= $r['jenis_laporan']==='Pengaduan'?'badge-pengaduan':'badge-aspirasi' ?>">
                  <?= strtoupper($r['jenis_laporan']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
              <td>
                <?php if ($r['status_laporan'] === 'selesai'): ?>
                <span class="badge badge-selesai">Selesai</span>
                <?php else: ?>
                <span class="badge" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;">Ditolak</span>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-size:12px;color:var(--text-muted);">
                  <?= htmlspecialchars($r['nama_admin_proses'] ?? '-') ?>
                  <?php if ($r['diproses_pada']): ?>
                  <br><span style="font-size:11px;"><?= date('d M Y H:i', strtotime($r['diproses_pada'])) ?></span>
                  <?php endif; ?>
                </span>
              </td>
              <td>
                <?php if ($r['status_laporan'] === 'selesai'): ?>
                <span style="font-size:12px;color:var(--text-muted);">
                  <?= htmlspecialchars($r['nama_admin_selesai'] ?? '-') ?>
                  <?php if ($r['selesai_pada']): ?>
                  <br><span style="font-size:11px;"><?= date('d M Y H:i', strtotime($r['selesai_pada'])) ?></span>
                  <?php endif; ?>
                </span>
                <?php else: ?>
                <span style="font-size:12px;color:var(--text-label);">-</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <a href="detail-histori.php?id=<?= $r['id_laporan'] ?>" class="action-icon" title="Lihat Detail">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
              </td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <span class="pagination-info">Menampilkan <?= min($offset+1,$total) ?>–<?= min($offset+$limit,$total) ?> dari <?= $total ?> data</span>
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