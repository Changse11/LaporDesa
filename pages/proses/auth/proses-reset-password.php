<?php
// proses auth: reset password
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/koneksi.php';

$token = $_GET['token'] ?? '';
if (empty($token) || !isset($_SESSION['reset_token'], $_SESSION['reset_id_user'], $_SESSION['reset_expiry']) || !hash_equals($_SESSION['reset_token'], $token) || time() >= $_SESSION['reset_expiry']) {
    $_SESSION['flash_error'] = 'Link reset tidak valid atau sudah kedaluwarsa.';
    header('Location: /RPL/pages/auth/lupa-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /RPL/pages/auth/reset-password.php?token=' . urlencode($token));
    exit;
}

$password   = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['konfirmasi'] ?? '';

if (strlen($password) < 8) {
    $_SESSION['flash_error'] = 'Password minimal 8 karakter.';
    header('Location: /RPL/pages/auth/reset-password.php?token=' . urlencode($token));
    exit;
}
if ($password !== $konfirmasi) {
    $_SESSION['flash_error'] = 'Konfirmasi password tidak cocok.';
    header('Location: /RPL/pages/auth/reset-password.php?token=' . urlencode($token));
    exit;
}

$hash    = password_hash($password, PASSWORD_BCRYPT);
$id_user = $_SESSION['reset_id_user'];

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
$stmt->bind_param('si', $hash, $id_user);

if ($stmt->execute()) {
    unset($_SESSION['reset_token'], $_SESSION['reset_id_user'], $_SESSION['reset_expiry']);
    $_SESSION['daftar_sukses'] = 'Password berhasil diubah. Silakan masuk.';
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
} else {
    $_SESSION['flash_error'] = 'Terjadi kesalahan. Coba lagi.';
    header('Location: /RPL/pages/auth/reset-password.php?token=' . urlencode($token));
    exit;
}
