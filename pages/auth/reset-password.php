<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error   = '';
$success = '';
$token_valid = false;

// Validasi token dari URL vs session
$token = $_GET['token'] ?? '';

if (
    !empty($token) &&
    isset($_SESSION['reset_token'], $_SESSION['reset_id_user'], $_SESSION['reset_expiry']) &&
    hash_equals($_SESSION['reset_token'], $token) &&
    time() < $_SESSION['reset_expiry']
) {
    $token_valid = true;
} else {
    $error = 'Link reset tidak valid atau sudah kedaluwarsa. Silakan ulangi dari halaman Lupa Password.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password   = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $hash    = password_hash($password, PASSWORD_BCRYPT);
        $id_user = $_SESSION['reset_id_user'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        $stmt->bind_param('si', $hash, $id_user);

        if ($stmt->execute()) {
            // Hapus token session agar tidak bisa dipakai lagi
            unset($_SESSION['reset_token'], $_SESSION['reset_id_user'], $_SESSION['reset_expiry']);
            $_SESSION['daftar_sukses'] = 'Password berhasil diubah. Silakan masuk.';
            header('Location: masuk.php');
            exit;
        } else {
            $error = 'Terjadi kesalahan. Coba lagi.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password – LaporDesa</title>
  <link rel="stylesheet" href="../../assets/css/auth.css" />
</head>
<body>
  <div class="card">

    <!-- Left Panel -->
    <div class="panel-left">
      <img src="../../assets/img/desa.jpg" alt="Foto Desa" />
      <div class="brand-box">
        <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="10" width="18" height="11" rx="1"/>
          <polyline points="3 10 12 3 21 10"/>
          <line x1="9" y1="21" x2="9" y2="14"/>
          <line x1="15" y1="21" x2="15" y2="14"/>
        </svg>
        <div class="brand-name">LaporDesa</div>
        <div class="brand-sub">Sistem Administrasi Warga<br>Terpadu</div>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="panel-right">
      <h1>Reset Password</h1>

      <?php if (!$token_valid): ?>
        <div class="error-expired">
          <p style="color:#c53030; margin-bottom:12px;">⚠️ <?= htmlspecialchars($error) ?></p>
          <a href="lupa-password.php">← Kembali ke Lupa Password</a>
        </div>

      <?php else: ?>
        <p class="subtitle">Buat kata sandi baru yang kuat untuk akun Anda.</p>

        <?php if ($error): ?>
          <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="?token=<?= htmlspecialchars($token) ?>">
          <div class="field">
            <label for="password_baru">Password Baru</label>
            <div class="password-wrapper">
              <input type="password" id="password_baru" name="password_baru" placeholder="Minimal 8 karakter" />
              <button type="button" class="toggle-password" data-target="password_baru" aria-label="Tampilkan password">
                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.36 18.36 0 0 1 4.06-5.06M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="field" style="margin-top: 16px;">
            <label for="konfirmasi">Konfirmasi Password Baru</label>
            <div class="password-wrapper">
              <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ketik ulang password baru" />
              <button type="button" class="toggle-password" data-target="konfirmasi" aria-label="Tampilkan password">
                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.36 18.36 0 0 1 4.06-5.06M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-primary">Simpan Password</button>
        </form>
      <?php endif; ?>

      <p class="footer-link" style="margin-top: 20px;">
        Kembali ke <a href="masuk.php">Halaman Masuk</a>
      </p>
    </div>

  </div>

  <script>
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        var willShow = input.type === 'password';
        input.type = willShow ? 'text' : 'password';
        btn.classList.toggle('is-visible', willShow);
        btn.setAttribute('aria-label', willShow ? 'Sembunyikan password' : 'Tampilkan password');
      });
    });
  </script>
</body>
</html>