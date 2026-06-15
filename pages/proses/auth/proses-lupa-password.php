<?php
// proses auth: lupa password
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /RPL/pages/auth/lupa-password.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Format email tidak valid.';
    header('Location: /RPL/pages/auth/lupa-password.php');
    exit;
}

$stmt = $conn->prepare("SELECT id_user, nama_lengkap FROM users WHERE email = ? AND status_akun = 'aktif' LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user  = $result->fetch_assoc();
    $token = bin2hex(random_bytes(32));
    $_SESSION['reset_token']   = $token;
    $_SESSION['reset_id_user'] = $user['id_user'];
    $_SESSION['reset_expiry']  = time() + 900; // 15 menit
    header("Location: /RPL/pages/auth/reset-password.php?token=$token");
    exit;
}

// Generic message
$_SESSION['flash_success'] = 'Jika email terdaftar, instruksi reset password telah dikirim.';
header('Location: /RPL/pages/auth/lupa-password.php');
exit;
