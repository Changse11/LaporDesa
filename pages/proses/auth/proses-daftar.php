<?php
// proses auth: daftar/register
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /RPL/pages/auth/daftar.php');
    exit;
}

$nik        = trim($_POST['nik'] ?? '');
$nama       = trim($_POST['nama'] ?? '');
$tempat     = trim($_POST['tempat'] ?? '');
$tgl_lahir  = $_POST['tgl_lahir'] ?? '';
$jk         = $_POST['jenis_kelamin'] ?? '';
$telp       = trim($_POST['telp'] ?? '');
$username   = trim($_POST['username'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';
$konfirmasi = $_POST['konfirmasi'] ?? '';

$errors = [];
if (!preg_match('/^\d{16}$/', $nik))            $errors['nik']           = 'NIK harus 16 digit angka.';
if (strlen($nama) < 3)                           $errors['nama']          = 'Nama terlalu pendek.';
if (empty($tempat))                              $errors['tempat']        = 'Tempat tinggal wajib diisi.';
if (empty($tgl_lahir))                           $errors['tgl_lahir']     = 'Tanggal lahir wajib diisi.';
if (!in_array($jk, ['L','P']))                   $errors['jenis_kelamin'] = 'Pilih jenis kelamin.';
if (!preg_match('/^08\d{8,11}$/', $telp))       $errors['telp']          = 'Format no. telp tidak valid (08xxxxxxxxxx).';
if (!preg_match('/^\w{4,20}$/', $username))      $errors['username']      = 'Username 4–20 karakter, hanya huruf/angka/underscore.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email']         = 'Format email tidak valid.';
if (strlen($password) < 8)                       $errors['password']      = 'Password minimal 8 karakter.';
if ($password !== $konfirmasi)                   $errors['konfirmasi']    = 'Konfirmasi password tidak cocok.';

if (!empty($errors)) {
    $_SESSION['daftar_errors'] = $errors;
    $_SESSION['daftar_old'] = [
        'nik'           => $nik,
        'nama'          => $nama,
        'tempat'        => $tempat,
        'tgl_lahir'     => $tgl_lahir,
        'jenis_kelamin' => $jk,
        'telp'          => $telp,
        'username'      => $username,
        'email'         => $email,
    ];
    header('Location: /RPL/pages/auth/daftar.php');
    exit;
}

// Cek duplikat
$stmt = $conn->prepare("SELECT id_user FROM users WHERE nik=? OR username=? OR email=? OR no_telp=?");
$stmt->bind_param('ssss', $nik, $username, $email, $telp);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $_SESSION['daftar_errors'] = ['Data sudah terdaftar.'];
    $_SESSION['daftar_old'] = [
        'nik'           => $nik,
        'nama'          => $nama,
        'tempat'        => $tempat,
        'tgl_lahir'     => $tgl_lahir,
        'jenis_kelamin' => $jk,
        'telp'          => $telp,
        'username'      => $username,
        'email'         => $email,
    ];
    header('Location: /RPL/pages/auth/daftar.php');
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare(
    "INSERT INTO users (nik, nama_lengkap, tempat_tinggal, tanggal_lahir, jenis_kelamin, no_telp, username, email, password)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssssssss', $nik, $nama, $tempat, $tgl_lahir, $jk, $telp, $username, $email, $hash);

if ($stmt->execute()) {
    $_SESSION['daftar_sukses'] = 'Pendaftaran berhasil! Silakan masuk.';
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
} else {
    $_SESSION['daftar_errors'] = ['Terjadi kesalahan. Coba lagi.'];
    $_SESSION['daftar_old'] = [
        'nik'           => $nik,
        'nama'          => $nama,
        'tempat'        => $tempat,
        'tgl_lahir'     => $tgl_lahir,
        'jenis_kelamin' => $jk,
        'telp'          => $telp,
        'username'      => $username,
        'email'         => $email,
    ];
    header('Location: /RPL/pages/auth/daftar.php');
    exit;
}
