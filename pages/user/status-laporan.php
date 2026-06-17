<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: /pages/auth/masuk.php'); exit;
}

$id_user       = $_SESSION['id_user'];
$filter_status = $_GET['status'] ?? '';
$search        = trim($_GET['q'] ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 8;
$offset        = ($page - 1) * $per_page;

$where_parts = ["l.id_user = $id_user"];
$params = []; $types = '';

if ($filter_status && in_array($filter_status, ['menunggu','diproses','selesai','ditolak'])) {
    $where_parts[] = 'l.status_laporan = ?';
    $params[] = $filter_status; $types .= 's';
}
if ($search !== '') {
    $where_parts[] = '(l.nomor_laporan LIKE ? OR l.judul LIKE ?)';
    $like = '%' . $conn->real_escape_string($search) . '%';
    $params[] = $like; $params[] = $like; $types .= 'ss';
}

$where_sql = 'WHERE ' . implode(' AND ', $where_parts);

$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM laporan l $where_sql");
if ($types) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_rows  = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = max(1, (int)ceil($total_rows / $per_page));
$stmt_count->close();

$rows = [];
$stmt_data = $conn->prepare("
    SELECT l.nomor_laporan, l.judul, l.jenis_laporan, l.status_laporan,
           l.tanggal_laporan, k.nama_kategori
    FROM laporan l
    JOIN kategori k ON l.id_kategori = k.id_kategori
    $where_sql
    ORDER BY l.tanggal_laporan DESC
    LIMIT $per_page OFFSET $offset
");
if ($types) $stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$res = $stmt_data->get_result();
while ($row = $res->fetch_assoc()) $rows[] = $row;
$stmt_data->close();

$sukses = '';
if (isset($_SESSION['laporan_sukses'])) {
    $sukses = $_SESSION['laporan_sukses'];
    unset($_SESSION['laporan_sukses']);
}

function statusBadge(string $s): string {
    $map = [
        'menunggu' => ['Menunggu', 'status-menunggu'],
        'diproses' => ['Diproses', 'status-proses'],
        'selesai'  => ['Selesai',  'status-selesai'],
        'ditolak'  => ['Ditolak',  'status-ditolak'],
    ];
    [$label, $cls] = $map[$s] ?? [ucfirst($s), 'status-menunggu'];
    return "<span class=\"status-dot $cls\">$label</span>";
}

include '../includes/nav.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Status Laporan – LaporDesa</title>
  <link rel="stylesheet" href="../../assets/css/user.css"/>
</head>
<body>

<main>

  <?php if ($sukses): ?>
    <div class="alert-success">✅ <?= $sukses ?></div>
  <?php endif; ?>

  <div class="top-bar animate">
    <div>
      <h1 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:4px">Status Laporan Saya</h1>
      <p style="color:var(--text-muted);font-size:0.88rem">Total <strong><?= $total_rows ?></strong> laporan ditemukan.</p>
    </div>
    <a href="laporan-form.php" class="btn btn-primary">+ Buat Laporan Baru</a>
  </div>

  <form method="GET" action="status-laporan.php">
    <div class="toolbar animate delay-1">
      <div class="search-wrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="q" placeholder="Cari nomor atau judul laporan..."
               value="<?= htmlspecialchars($search) ?>"/>
      </div>
      <div class="filter-wrap">
        <label>Filter:</label>
        <select name="status" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <?php foreach (['menunggu'=>'Menunggu','diproses'=>'Diproses','selesai'=>'Selesai','ditolak'=>'Ditolak'] as $val=>$lbl): ?>
            <option value="<?= $val ?>" <?= $filter_status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($search || $filter_status): ?>
          <a href="status-laporan.php" style="font-size:0.8rem;color:var(--text-muted)">Reset</a>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <div class="table-card animate delay-2">
    <?php if (empty($rows)): ?>
      <div class="empty-state" style="text-align:center;align-items:center;padding:48px 24px;">
        <h2>Belum ada laporan</h2>
        <p>Sepertinya Anda belum mengajukan laporan apa pun.</p>
        <a href="laporan-form.php" class="btn btn-primary" style="margin-top:8px;">+ Buat Laporan Baru</a>
      </div>
    <?php else: ?>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>No. Laporan</th>
              <th>Tgl Kirim</th>
              <th>Judul</th>
              <th>Kategori</th>
              <th>Jenis</th>
              <th>Status</th>
              <th>Detail</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td class="td-id"><?= htmlspecialchars($r['nomor_laporan']) ?></td>
              <td class="td-date"><?= date('d M Y', strtotime($r['tanggal_laporan'])) ?></td>
              <td class="td-title"><?= htmlspecialchars($r['judul']) ?></td>
              <td class="td-category"><?= htmlspecialchars($r['nama_kategori']) ?></td>
              <td>
                <span class="badge <?= $r['jenis_laporan'] === 'Pengaduan' ? 'badge-yellow' : 'badge-blue' ?>">
                  <?= htmlspecialchars($r['jenis_laporan']) ?>
                </span>
              </td>
              <td><?= statusBadge($r['status_laporan']) ?></td>
              <td>
                <a href="detail-laporan.php?nomor=<?= urlencode($r['nomor_laporan']) ?>" class="btn-detail">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  Lihat
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <p>Menampilkan <?= $offset + 1 ?>–<?= min($offset + $per_page, $total_rows) ?> dari <?= $total_rows ?> laporan</p>
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
          <?php $base = '?page=%d' . ($filter_status ? "&status=$filter_status" : '') . ($search ? "&q=" . urlencode($search) : ''); ?>
          <a href="<?= sprintf($base, max(1, $page-1)) ?>" class="pag-btn" <?= $page<=1 ? 'style="opacity:.4;pointer-events:none"' : '' ?>>‹</a>
          <?php for ($i=1; $i<=$total_pages; $i++): ?>
            <a href="<?= sprintf($base, $i) ?>" class="pag-btn <?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a href="<?= sprintf($base, min($total_pages, $page+1)) ?>" class="pag-btn" <?= $page>=$total_pages ? 'style="opacity:.4;pointer-events:none"' : '' ?>>›</a>
        </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

</main>

<?php include '../includes/footer.php'; ?>
<script src="../../assets/js/script.js"></script>
</body>
</html>