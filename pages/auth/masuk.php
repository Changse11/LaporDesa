<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Kalau sudah login, redirect langsung
if (isset($_SESSION['id_user'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? '/RPL/pages/admin/dashboard.php' : '/RPL/pages/user/home.php'));
    exit;
}

$error   = '';
$success = '';

// Ambil pesan sukses dari daftar
if (isset($_SESSION['daftar_sukses'])) {
    $success = $_SESSION['daftar_sukses'];
    unset($_SESSION['daftar_sukses']);
}

$old_kredensial = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kredensial = trim($_POST['kredensial'] ?? '');
    $password   = $_POST['password'] ?? '';
    $old_kredensial = $kredensial;

    if (empty($kredensial) || empty($password)) {
        $error = 'Kredensial dan password wajib diisi.';
    } else {
        // Cari user berdasarkan email, username, atau no_telp
        $stmt = $conn->prepare(
            "SELECT id_user, nama_lengkap, password, role, status_akun
             FROM users
             WHERE email = ? OR username = ? OR no_telp = ?
             LIMIT 1"
        );
        $stmt->bind_param('sss', $kredensial, $kredensial, $kredensial);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status_akun'] === 'nonaktif') {
                $error = 'Akun Anda telah dinonaktifkan. Hubungi admin.';
            } else {
                $isValidPassword = password_verify($password, $user['password']);
                $useLegacyPlain  = !$isValidPassword && $user['password'] === $password;

                if ($isValidPassword || $useLegacyPlain) {
                    // Jika password lama masih disimpan sebagai plain text,
                    // hash ulang password dan simpan ke database.
                    if ($useLegacyPlain || password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
                        $newHash = password_hash($password, PASSWORD_BCRYPT);
                        $update  = $conn->prepare('UPDATE users SET password = ? WHERE id_user = ?');
                        $update->bind_param('si', $newHash, $user['id_user']);
                        $update->execute();
                        $update->close();
                    }

                    // Login sukses — simpan session
                    $_SESSION['id_user'] = $user['id_user'];
                    $_SESSION['nama']    = $user['nama_lengkap'];
                    $_SESSION['role']    = $user['role'];
                    $_SESSION['user']    = [
                        'id_user'      => $user['id_user'],
                        'nama_lengkap' => $user['nama_lengkap'],
                        'role'         => $user['role'],
                    ];

                    // Redirect by role
                    if ($user['role'] === 'admin') {
                        header('Location: /RPL/pages/admin/dashboard.php');
                    } else {
                        header('Location: /RPL/pages/user/home.php');
                    }
                    exit;
                }

                $error = 'Password salah. Coba lagi.';
            }
        } else {
            $error = 'Akun tidak ditemukan. Periksa kembali kredensial Anda.';
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
  <title>Masuk – LaporKades</title>
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
      <h1>Masuk</h1>
      <p class="subtitle">Selamat datang kembali. Silakan masuk untuk melanjutkan.</p>

      <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="../proses/auth/proses-masuk.php">
        <div class="field">
          <label for="kredensial">Email, No. Telp, atau Username</label>
          <input type="text" id="kredensial" name="kredensial"
                 placeholder="Masukkan kredensial Anda"
                 value="<?= htmlspecialchars($old_kredensial) ?>" />
        </div>

        <div class="field" style="margin-top: 16px;">
          <div class="label-row">
            <label for="password">Password</label>
            <a href="lupa-password.php">Lupa Password?</a>
          </div>
          <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" />
        </div>

        <button type="submit" class="btn-primary">Masuk</button>
      </form>

      <div class="divider">Tidak Memiliki Akun?</div>

      <button class="btn-secondary" onclick="window.location.href='daftar.php'">Daftar Sekarang</button>
    </div>

  </div>
</body>
</html>