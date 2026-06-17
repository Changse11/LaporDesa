<nav class="navbar">
  <a href="/RPL/pages/user/home.php" class="navbar-brand">LaporDesa</a>

  <ul class="navbar-nav" id="navMenu">
    <?php
      $current = basename($_SERVER['PHP_SELF']);
      $nav = [
        'home.php'           => ['Home',      '/RPL/pages/user/home.php'],
        'tentang.php'        => ['Tentang',   '/RPL/pages/user/tentang.php'],
        'dashboard.php'      => ['Dashboard', '/RPL/pages/user/dashboard.php'],
        'laporan-form.php'   => ['Laporan',   '/RPL/pages/user/laporan-form.php'],
        'status-laporan.php' => ['Status',    '/RPL/pages/user/status-laporan.php'],
        'kontak.php'         => ['Kontak',    '/RPL/pages/user/kontak.php'],
      ];
      foreach ($nav as $file => [$label, $href]):
    ?>
      <li>
        <a href="<?= $href ?>" class="<?= $current === $file ? 'active' : '' ?>">
          <?= $label ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="navbar-right">
    <?php if(isset($_SESSION['id_user'])): ?>
      <div class="avatar-wrap">
        <button class="avatar-btn" onclick="toggleDropdown()" title="Profil">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </button>
        <div class="avatar-dropdown" id="avatarDropdown">
          <p>👤 <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?></p>
          <a href="/RPL/pages/auth/logout.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Logout
          </a>
        </div>
      </div>
    <?php else: ?>
      <a href="/RPL/pages/auth/masuk.php" class="btn-nav-login">Login</a>
      <a href="/RPL/pages/auth/daftar.php" class="btn-nav-register">Register</a>
    <?php endif; ?>

    <!-- Burger button — hanya tampil di mobile -->
    <button class="burger-btn" id="burgerBtn" onclick="toggleBurger()" aria-label="Menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</nav>

<!-- Overlay backdrop -->
<div class="nav-overlay" id="navOverlay" onclick="closeBurger()"></div>