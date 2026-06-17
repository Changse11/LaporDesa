<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Kalau sudah login, redirect
if (isset($_SESSION['id_user'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? '/pages/admin/dashboard.php' : '/pages/user/home.php'));
    exit;
}

$errors = [];
$old    = [];

if (isset($_SESSION['daftar_errors'])) {
    $errors = $_SESSION['daftar_errors'];
    unset($_SESSION['daftar_errors']);
}
if (isset($_SESSION['daftar_old'])) {
    $old = $_SESSION['daftar_old'];
    unset($_SESSION['daftar_old']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input
    $nik            = trim($_POST['nik'] ?? '');
    $nama           = trim($_POST['nama'] ?? '');
    $tempat         = trim($_POST['tempat'] ?? '');
    $tgl_lahir      = $_POST['tgl_lahir'] ?? '';
    $jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
    $telp           = trim($_POST['telp'] ?? '');
    $username       = trim($_POST['username'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $password       = $_POST['password'] ?? '';
    $konfirmasi     = $_POST['konfirmasi'] ?? '';

    // Simpan untuk repopulate form
    $old = compact('nik','nama','tempat','tgl_lahir','jenis_kelamin','telp','username','email');

    // Validasi
    if (!preg_match('/^\d{16}$/', $nik))            $errors['nik']           = 'NIK harus 16 digit angka.';
    if (strlen($nama) < 3)                           $errors['nama']          = 'Nama terlalu pendek.';
    if (empty($tempat))                              $errors['tempat']        = 'Tempat tinggal wajib diisi.';
    if (empty($tgl_lahir))                           $errors['tgl_lahir']     = 'Tanggal lahir wajib diisi.';
    if (!in_array($jenis_kelamin, ['L','P']))        $errors['jenis_kelamin'] = 'Pilih jenis kelamin.';
    if (!preg_match('/^08\d{8,11}$/', $telp))       $errors['telp']          = 'Format no. telp tidak valid (08xxxxxxxxxx).';
    if (!preg_match('/^\w{4,20}$/', $username))      $errors['username']      = 'Username 4–20 karakter, hanya huruf/angka/underscore.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email']         = 'Format email tidak valid.';
    if (strlen($password) < 8)                       $errors['password']      = 'Password minimal 8 karakter.';
    if ($password !== $konfirmasi)                   $errors['konfirmasi']    = 'Konfirmasi password tidak cocok.';

    // Cek duplikat di DB
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id_user FROM users WHERE nik=? OR username=? OR email=? OR no_telp=?");
        $stmt->bind_param('ssss', $nik, $username, $email, $telp);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Cek satu per satu untuk pesan spesifik
            $checks = [
                'nik'      => "SELECT id_user FROM users WHERE nik='$nik'",
                'username' => "SELECT id_user FROM users WHERE username='$username'",
                'email'    => "SELECT id_user FROM users WHERE email='$email'",
                'telp'     => "SELECT id_user FROM users WHERE no_telp='$telp'",
            ];
            $msgs = ['nik'=>'NIK sudah terdaftar.','username'=>'Username sudah digunakan.','email'=>'Email sudah terdaftar.','telp'=>'No. telp sudah terdaftar.'];
            foreach ($checks as $field => $q) {
                $r = $conn->query($q);
                if ($r && $r->num_rows > 0) $errors[$field] = $msgs[$field];
            }
        }
        $stmt->close();
    }

    // Simpan ke DB
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare(
            "INSERT INTO users (nik, nama_lengkap, tempat_tinggal, tanggal_lahir, jenis_kelamin, no_telp, username, email, password)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('sssssssss', $nik, $nama, $tempat, $tgl_lahir, $jenis_kelamin, $telp, $username, $email, $hash);

        if ($stmt->execute()) {
            $_SESSION['daftar_sukses'] = 'Pendaftaran berhasil! Silakan masuk.';
            header('Location: masuk.php');
            exit;
        } else {
            $errors['general'] = 'Terjadi kesalahan. Coba lagi.';
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
  <title>Daftar – LaporDesa</title>
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
      <h1>Daftar</h1>

      <?php
        $globalErrors = [];
        foreach ($errors as $key => $message) {
            if (is_int($key) || $key === 'general') {
                $globalErrors[] = $message;
            }
        }
      ?>
      <?php if (!empty($globalErrors)): ?>
        <div class="alert-error">
          <?php foreach ($globalErrors as $message): ?>
            <p><?= htmlspecialchars($message) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="../proses/auth/proses-daftar.php">
        <div class="form-grid">

          <div class="field">
            <label for="nik">NIK</label>
            <input type="text" id="nik" name="nik" maxlength="16"
                   placeholder="Masukkan 16 digit NIK"
                   value="<?= htmlspecialchars($old['nik'] ?? '') ?>"
                   class="<?= isset($errors['nik']) ? 'is-error' : '' ?>" />
            <?php if (isset($errors['nik'])): ?><span class="error-msg"><?= $errors['nik'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" placeholder="Sesuai KTP"
                   value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                   class="<?= isset($errors['nama']) ? 'is-error' : '' ?>" />
            <?php if (isset($errors['nama'])): ?><span class="error-msg"><?= $errors['nama'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="tempat">Tempat Tinggal</label>
            <input type="text" id="tempat" name="tempat" placeholder="Alamat saat ini"
                   value="<?= htmlspecialchars($old['tempat'] ?? '') ?>"
                   class="<?= isset($errors['tempat']) ? 'is-error' : '' ?>" />
            <?php if (isset($errors['tempat'])): ?><span class="error-msg"><?= $errors['tempat'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="tgl_lahir">Tanggal Lahir</label>
            <input type="date" id="tgl_lahir" name="tgl_lahir"
                   value="<?= htmlspecialchars($old['tgl_lahir'] ?? '') ?>"
                   class="<?= isset($errors['tgl_lahir']) ? 'is-error' : '' ?>" />
            <?php if (isset($errors['tgl_lahir'])): ?><span class="error-msg"><?= $errors['tgl_lahir'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <div class="select-wrapper">
              <select id="jenis_kelamin" name="jenis_kelamin"
                      class="<?= isset($errors['jenis_kelamin']) ? 'is-error' : '' ?>">
                <option value="" disabled <?= empty($old['jenis_kelamin']) ? 'selected' : '' ?>>Pilih Jenis Kelamin</option>
                <option value="L" <?= ($old['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="P" <?= ($old['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
              </select>
            </div>
            <?php if (isset($errors['jenis_kelamin'])): ?><span class="error-msg"><?= $errors['jenis_kelamin'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="telp">No. Telp Aktif</label>
            <input type="tel" id="telp" name="telp" placeholder="08xxxxxxxxxx"
                   value="<?= htmlspecialchars($old['telp'] ?? '') ?>"
                   class="<?= isset($errors['telp']) ? 'is-error' : '' ?>" />
            <?php if (isset($errors['telp'])): ?><span class="error-msg"><?= $errors['telp'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Pilih username"
                   value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                   class="<?= isset($errors['username']) ? 'is-error' : '' ?>" />
            <?php if (isset($errors['username'])): ?><span class="error-msg"><?= $errors['username'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="alamat@email.com"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                   class="<?= isset($errors['email']) ? 'is-error' : '' ?>" />
            <?php if (isset($errors['email'])): ?><span class="error-msg"><?= $errors['email'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <div class="password-wrapper">
              <input type="password" id="password" name="password" placeholder="Minimal 8 karakter"
                     class="<?= isset($errors['password']) ? 'is-error' : '' ?>" />
              <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password">
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
            <?php if (isset($errors['password'])): ?><span class="error-msg"><?= $errors['password'] ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="konfirmasi">Konfirmasi Password</label>
            <div class="password-wrapper">
              <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ketik ulang password"
                     class="<?= isset($errors['konfirmasi']) ? 'is-error' : '' ?>" />
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
            <?php if (isset($errors['konfirmasi'])): ?><span class="error-msg"><?= $errors['konfirmasi'] ?></span><?php endif; ?>
          </div>

        </div>

        <button type="submit" class="btn-primary">Daftar</button>
      </form>

      <p class="footer-link">
        Sudah punya akun? <a href="masuk.php">Masuk</a>
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