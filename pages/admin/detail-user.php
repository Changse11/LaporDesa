<?php
// pages/admin/detail-user.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: kelola-users.php'); exit; }

// Handle toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $aksi = $_POST['aksi'];
    $baru = ($aksi === 'aktif') ? 'nonaktif' : 'aktif';
    $stmt = $conn->prepare("UPDATE users SET status_akun = ? WHERE id_user = ? AND role = 'user'");
    $stmt->bind_param("si", $baru, $id);
    $stmt->execute();
    $_SESSION['flash'] = "Status akun berhasil diubah menjadi <strong>$baru</strong>.";
    header("Location: detail-user.php?id=$id");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ? AND role = 'user'");
$stmt->bind_param('i', $id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
if (!$u) { header('Location: kelola-users.php'); exit; }

// Jumlah laporan user ini
$jml = $conn->query("SELECT COUNT(*) as c FROM laporan WHERE id_user = $id")->fetch_assoc()['c'];

$page_title = "Detail User";
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
    <h1 class="page-title">Kelola Users</h1>

    <div class="section-header">
      <div class="section-label">Detail User</div>
      <div class="section-desc">Informasi lengkap terkait akun pengguna terdaftar.</div>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert-success" style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
      <?= $_SESSION['flash'] ?><?php unset($_SESSION['flash']); ?>
    </div>
    <?php endif; ?>

    <div class="detail-layout">
      <!-- Profile Card -->
      <div class="user-profile-card">
        <div class="user-avatar">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="user-name"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
        <div class="user-role">Masyarakat Umum</div>
        <div class="user-meta">
          <div class="user-meta-row">
            <span>Status Akun</span>
            <?php if ($u['status_akun'] === 'aktif'): ?>
            <span class="status-dot">Aktif</span>
            <?php else: ?>
            <span style="color:#b91c1c;font-weight:600;display:inline-flex;align-items:center;gap:5px;">
              <span style="width:7px;height:7px;background:#ef4444;border-radius:50%;display:inline-block;"></span>Nonaktif
            </span>
            <?php endif; ?>
          </div>
          <div class="user-meta-row">
            <span>Terdaftar Sejak</span>
            <span><?= date('d M Y', strtotime($u['terdaftar_sejak'])) ?></span>
          </div>
          <div class="user-meta-row">
            <span>Total Laporan</span>
            <span><?= $jml ?> laporan</span>
          </div>
        </div>

        <!-- Toggle Status -->
        <form method="POST" action="../proses/admin/proses-users.php" style="width:100%;margin-top:8px;" onsubmit="return confirm('<?= $u['status_akun']==='aktif'?'Nonaktifkan':'Aktifkan' ?> akun ini?')">
          <input type="hidden" name="toggle_status" value="1">
          <input type="hidden" name="id_user" value="<?= $id ?>">
          <input type="hidden" name="aksi" value="<?= $u['status_akun'] ?>">
          <?php if ($u['status_akun'] === 'aktif'): ?>
          <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Nonaktifkan Akun
          </button>
          <?php else: ?>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Aktifkan Kembali
          </button>
          <?php endif; ?>
        </form>
      </div>

      <!-- Data Diri -->
      <div class="detail-card">
        <div class="top-accent"></div>
        <div class="form-grid">
          <div class="form-group">
            <label>
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
              NIK
            </label>
            <input class="form-control" readonly value="<?= htmlspecialchars($u['nik']) ?>" />
          </div>
          <div class="form-group">
            <label>
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Nama Lengkap
            </label>
            <input class="form-control" readonly value="<?= htmlspecialchars($u['nama_lengkap']) ?>" />
          </div>
          <div class="form-group">
            <label>
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              Tempat Tinggal
            </label>
            <input class="form-control" readonly value="<?= htmlspecialchars($u['tempat_tinggal']) ?>" />
          </div>
          <div class="form-group">
            <label>
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Tanggal Lahir
            </label>
            <input class="form-control" readonly value="<?= date('d F Y', strtotime($u['tanggal_lahir'])) ?>" />
          </div>
          <div class="form-group">
            <label>Jenis Kelamin</label>
            <input class="form-control" readonly value="<?= $u['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>" />
          </div>
          <div class="form-group">
            <label>
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 9.5a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 0h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8 7.09a16 16 0 0 0 6 6l.39-.38a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 15z"/></svg>
              Nomor Telepon
            </label>
            <input class="form-control" readonly value="<?= htmlspecialchars($u['no_telp']) ?>" />
          </div>
          <div class="form-group">
            <label>Username</label>
            <input class="form-control" readonly value="<?= htmlspecialchars($u['username']) ?>" />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input class="form-control" readonly value="<?= htmlspecialchars($u['email']) ?>" />
          </div>
        </div>

        <div class="form-actions">
          <a href="kelola-users.php" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
          </a>
        </div>
      </div>
    </div>
  </main>
</div>

</body>
</html>