<?php
// proses auth: login
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

$kredensial = trim($_POST['kredensial'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($kredensial) || empty($password)) {
    $_SESSION['flash_error'] = 'Kredensial dan password wajib diisi.';
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

$stmt = $conn->prepare(
    "SELECT id_user, nama_lengkap, password, role, status_akun
     FROM users
     WHERE email = ? OR username = ? OR no_telp = ?
     LIMIT 1"
);
$stmt->bind_param('sss', $kredensial, $kredensial, $kredensial);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['flash_error'] = 'Akun tidak ditemukan. Periksa kembali kredensial Anda.';
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

$user = $result->fetch_assoc();

if ($user['status_akun'] === 'nonaktif') {
    $_SESSION['flash_error'] = 'Akun Anda telah dinonaktifkan. Hubungi admin.';
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

$isValidPassword = password_verify($password, $user['password']);
$useLegacyPlain  = !$isValidPassword && $user['password'] === $password;

if (!($isValidPassword || $useLegacyPlain)) {
    $_SESSION['flash_error'] = 'Password salah. Coba lagi.';
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

// Rehash if needed or legacy plain
if ($useLegacyPlain || password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
    $newHash = password_hash($password, PASSWORD_BCRYPT);
    $update  = $conn->prepare('UPDATE users SET password = ? WHERE id_user = ?');
    $update->bind_param('si', $newHash, $user['id_user']);
    $update->execute();
    $update->close();
}

// Set session
$_SESSION['id_user'] = $user['id_user'];
$_SESSION['nama']    = $user['nama_lengkap'];
$_SESSION['role']    = $user['role'];
$_SESSION['user']    = [
    'id_user'      => $user['id_user'],
    'nama_lengkap' => $user['nama_lengkap'],
    'role'         => $user['role'],
];

// Redirect
if ($user['role'] === 'admin') {
    header('Location: /RPL/pages/admin/dashboard.php');
} else {
    header('Location: /RPL/pages/user/home.php');
}
exit;
