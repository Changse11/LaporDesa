<?php
// proses admin: toggle user status (aktif <-> nonaktif)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['toggle_status'])) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/RPL/pages/admin/kelola-users.php'));
    exit;
}

$id_user = (int)($_POST['id_user'] ?? 0);
$aksi    = $_POST['aksi'] ?? '';
if (!$id_user) {
    $_SESSION['flash'] = 'ID user tidak valid.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/RPL/pages/admin/kelola-users.php'));
    exit;
}

$baru = ($aksi === 'aktif') ? 'nonaktif' : 'aktif';
$stmt = $conn->prepare("UPDATE users SET status_akun = ? WHERE id_user = ? AND role = 'user'");
$stmt->bind_param('si', $baru, $id_user);
$stmt->execute();

$_SESSION['flash'] = "Status akun berhasil diubah menjadi <strong>$baru</strong>.";
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/RPL/pages/admin/kelola-users.php'));
exit;
