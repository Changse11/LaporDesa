<?php
// includes/nav.php - Admin sidebar navigation
// Deteksi halaman aktif
$current = basename($_SERVER['PHP_SELF']);
$active_dash  = in_array($current, ['dashboard.php']) ? 'active' : '';
$active_users = in_array($current, ['kelola-users.php', 'detail-user.php']) ? 'active' : '';
$active_lap   = in_array($current, ['laporan.php', 'detail-laporan-belum.php', 'detail-laporan-sudah.php']) ? 'active' : '';
$active_hist  = in_array($current, ['histori.php', 'detail-histori.php']) ? 'active' : '';

$admin_name = $_SESSION['user']['nama_lengkap'] ?? 'Admin';
?>
<aside class="sidebar">
  <div class="sidebar-logo">LaporKades</div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="<?= $active_dash ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="kelola-users.php" class="<?= $active_users ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Kelola Users
    </a>
    <a href="laporan.php" class="<?= $active_lap ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      Laporan
    </a>
    <a href="histori.php" class="<?= $active_hist ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 0 .5-4.5"/><polyline points="3 3 3 8 8 8"/></svg>
      Histori
    </a>
  </nav>
  <div class="sidebar-footer">
    <div style="padding:0 10px 8px;font-size:11px;color:var(--text-label);">
      Login sebagai <strong style="color:var(--text-muted);"><?= htmlspecialchars($admin_name) ?></strong>
    </div>
    <a href="../auth/logout.php" style="font-size:15px;padding:5px 10px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </div>
</aside>