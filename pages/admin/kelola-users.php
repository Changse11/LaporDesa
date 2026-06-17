<?php
// pages/admin/kelola-users.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

// Handle toggle status akun via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $id    = (int)$_POST['id_user'];
    $aksi  = $_POST['aksi']; // 'aktif' atau 'nonaktif'
    $baru  = ($aksi === 'aktif') ? 'nonaktif' : 'aktif';
    $stmt  = $conn->prepare("UPDATE users SET status_akun = ? WHERE id_user = ? AND role = 'user'");
    $stmt->bind_param("si", $baru, $id);
    $stmt->execute();
    $_SESSION['flash'] = "Status akun berhasil diubah menjadi <strong>$baru</strong>.";
    header('Location: kelola-users.php');
    exit;
}

// Search & Pagination
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE role = 'user'";
$params = [];
$types  = '';

if ($search !== '') {
    $where   .= " AND (nama_lengkap LIKE ? OR email LIKE ? OR username LIKE ?)";
    $like     = "%$search%";
    $params   = [$like, $like, $like];
    $types    = 'sss';
}

// Total
$count_sql = "SELECT COUNT(*) as c FROM users $where";
if ($params) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_rows = $stmt->get_result()->fetch_assoc()['c'];
} else {
    $total_rows = $conn->query($count_sql)->fetch_assoc()['c'];
}
$total_pages = ceil($total_rows / $limit);

// Data
$sql = "SELECT id_user, nama_lengkap, email, username, status_akun, terdaftar_sejak
        FROM users $where ORDER BY terdaftar_sejak DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($params) {
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$users = $stmt->get_result();

$page_title = "Kelola User";
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
    <h1 class="page-title">Kelola Users</h1>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert-success" style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
      <?= $_SESSION['flash'] ?>
      <?php unset($_SESSION['flash']); ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3>Daftar Pengguna Terdaftar</h3>
        <form method="GET" action="">
          <div class="search-box">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" placeholder="Cari nama, email, username..." value="<?= htmlspecialchars($search) ?>" />
          </div>
        </form>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Lengkap</th>
              <th>Email</th>
              <th>Terdaftar</th>
              <th>Status</th>
              <th style="text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($users->num_rows === 0): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px;">Tidak ada user ditemukan</td></tr>
            <?php else: $no = $offset + 1; while ($u = $users->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><strong><?= htmlspecialchars($u['nama_lengkap']) ?></strong>
                <div style="font-size:11px;color:var(--text-muted);">@<?= htmlspecialchars($u['username']) ?></div>
              </td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= date('d M Y', strtotime($u['terdaftar_sejak'])) ?></td>
              <td>
                <?php if ($u['status_akun'] === 'aktif'): ?>
                <span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;">Aktif</span>
                <?php else: ?>
                <span class="badge" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                  <a href="detail-user.php?id=<?= $u['id_user'] ?>" class="btn btn-secondary" style="padding:4px 12px;font-size:12px;">Lihat</a>
                  <form method="POST" action="../proses/admin/proses-users.php" onsubmit="return confirm('<?= $u['status_akun'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?> akun ini?')">
                    <input type="hidden" name="toggle_status" value="1">
                    <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                    <input type="hidden" name="aksi" value="<?= $u['status_akun'] ?>">
                    <?php if ($u['status_akun'] === 'aktif'): ?>
                    <button type="submit" class="btn btn-danger" style="padding:4px 12px;font-size:12px;">Nonaktifkan</button>
                    <?php else: ?>
                    <button type="submit" class="btn btn-primary" style="padding:4px 12px;font-size:12px;">Aktifkan</button>
                    <?php endif; ?>
                  </form>
                </div>
              </td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <span class="pagination-info">Menampilkan <?= min($offset+1, $total_rows) ?>–<?= min($offset+$limit, $total_rows) ?> dari <?= $total_rows ?> pengguna</span>
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