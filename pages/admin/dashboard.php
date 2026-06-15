<?php
// pages/admin/dashboard.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/masuk.php');
    exit;
}

require_once '../lib/koneksi.php';

$admin = $_SESSION['user'];

$total     = $conn->query("SELECT COUNT(*) as c FROM laporan")->fetch_assoc()['c'];
$bulan_ini = $conn->query("SELECT COUNT(*) as c FROM laporan WHERE MONTH(tanggal_laporan)=MONTH(NOW()) AND YEAR(tanggal_laporan)=YEAR(NOW())")->fetch_assoc()['c'];
$hari_ini  = $conn->query("SELECT COUNT(*) as c FROM laporan WHERE DATE(tanggal_laporan)=CURDATE()")->fetch_assoc()['c'];

$laporan_baru = $conn->query("
    SELECT l.*, u.nama_lengkap, k.nama_kategori
    FROM laporan l JOIN users u ON l.id_user=u.id_user JOIN kategori k ON l.id_kategori=k.id_kategori
    WHERE l.status_laporan='menunggu' ORDER BY l.tanggal_laporan DESC LIMIT 5
");

function queryPrioritas($conn, $p) {
    return $conn->query("
        SELECT l.*, u.nama_lengkap, k.nama_kategori, a.nama_lengkap as nama_admin
        FROM laporan l JOIN users u ON l.id_user=u.id_user JOIN kategori k ON l.id_kategori=k.id_kategori
        LEFT JOIN users a ON l.diproses_oleh=a.id_user
        WHERE l.status_laporan='diproses' AND l.prioritas='$p'
        ORDER BY l.diproses_pada DESC LIMIT 3
    ");
}

$lap_tinggi = queryPrioritas($conn, 'Tinggi');
$lap_sedang = queryPrioritas($conn, 'Sedang');
$lap_rendah = queryPrioritas($conn, 'Rendah');

$page_title = "Dashboard Admin";

function renderPrioritasCard($result, $label, $color, $badge_class, $link_detail) {
    echo '<div class="card" style="margin-bottom:0;">';
    echo '<div class="card-header" style="padding:12px 16px 10px;">';
    echo '<h3 style="font-size:13px;color:'.$color.';">'.$label.'</h3>';
    echo '<a href="laporan.php?tab=sudah" class="card-link">Semua →</a>';
    echo '</div><div class="table-wrap"><table>';
    echo '<thead><tr><th>No</th><th>Judul</th><th>Admin</th></tr></thead><tbody>';
    if ($result->num_rows === 0) {
        echo '<tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:16px;font-size:12px;">Tidak ada</td></tr>';
    } else {
        $no = 1;
        while ($r = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>'.$no++.'</td>';
            echo '<td><a class="report-title" style="font-size:12px;" href="'.$link_detail.'?id='.$r['id_laporan'].'">'.htmlspecialchars($r['judul']).'</a>';
            echo '<div class="report-by">'.htmlspecialchars($r['nama_kategori']).'</div></td>';
            echo '<td style="font-size:11px;color:var(--text-muted);">'.htmlspecialchars($r['nama_admin'] ?? '-').'</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table></div></div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?> - LaporKades</title>
  <link rel="stylesheet" href="../../assets/css/admin.css"/>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/../includes/nav-admin.php'; ?>
  <main class="main">
    <h1 class="page-title">Dashboard</h1>

    <div class="stat-cards">
      <div class="stat-card">
        <div class="stat-card-info"><label>Jumlah Laporan Keseluruhan</label><span><?= number_format($total) ?></span></div>
        <div class="stat-card-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-info"><label>Jumlah Laporan Bulan Ini</label><span><?= number_format($bulan_ini) ?></span></div>
        <div class="stat-card-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-info"><label>Jumlah Laporan Hari Ini</label><span><?= number_format($hari_ini) ?></span></div>
        <div class="stat-card-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
      </div>
    </div>

    <!-- Laporan Baru Masuk -->
    <div class="card">
      <div class="card-header">
        <h3>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 0 .5-4.5"/></svg>
          Laporan Baru Masuk
        </h3>
        <a href="laporan.php" class="card-link">Lihat Semua →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>No</th><th>Judul Laporan</th><th>Kategori</th><th>Status</th></tr></thead>
          <tbody>
            <?php if ($laporan_baru->num_rows === 0): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px;">Tidak ada laporan baru</td></tr>
            <?php else: $no=1; while($r=$laporan_baru->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                <a class="report-title" href="detail-laporan-belum.php?id=<?= $r['id_laporan'] ?>"><?= htmlspecialchars($r['judul']) ?></a>
                <div class="report-by">Oleh: <?= htmlspecialchars($r['nama_lengkap']) ?></div>
              </td>
              <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
              <td><span class="badge badge-menunggu">Menunggu</span></td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 3 Tabel Prioritas berdampingan -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
      <?php
        renderPrioritasCard($lap_tinggi, '🔴 Prioritas Tinggi', '#b91c1c', 'badge-tinggi', 'detail-laporan-sudah.php');
        renderPrioritasCard($lap_sedang, '🟡 Prioritas Sedang', '#d97706', 'badge-sedang', 'detail-laporan-sudah.php');
        renderPrioritasCard($lap_rendah, '🟢 Prioritas Rendah', '#15803d', 'badge-rendah', 'detail-laporan-sudah.php');
      ?>
    </div>

  </main>
</div>
</body>
</html>