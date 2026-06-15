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
  <title>Reset Password – LaporKades</title>
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
        <div class="brand-name">LaporKades</div>
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
            <input type="password" id="password_baru" name="password_baru" placeholder="Minimal 8 karakter" />
          </div>

          <div class="field" style="margin-top: 16px;">
            <label for="konfirmasi">Konfirmasi Password Baru</label>
            <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ketik ulang password baru" />
          </div>

          <button type="submit" class="btn-primary">Simpan Password</button>
        </form>
      <?php endif; ?>

      <p class="footer-link" style="margin-top: 20px;">
        Kembali ke <a href="masuk.php">Halaman Masuk</a>
      </p>
    </div>

  </div>
</body>
</html>