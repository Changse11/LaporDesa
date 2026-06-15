<?php
// proses user: kirim laporan
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /RPL/pages/user/laporan-form.php');
    exit;
}

if (!isset($_SESSION['id_user'])) {
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}

$id_user = $_SESSION['id_user'];
$jenis       = $_POST['jenis']    ?? '';
$judul       = trim($_POST['judul'] ?? '');
$isi         = trim($_POST['isi']   ?? '');
$id_kategori = (int)($_POST['kategori'] ?? 0);
$tgl         = $_POST['tgl']    ?? '';
$lokasi      = trim($_POST['lokasi'] ?? '');

$errors = [];
if (!in_array($jenis, ['Pengaduan','Aspirasi'])) $errors[] = 'Pilih jenis laporan.';
if (strlen($judul) < 5)                          $errors[] = 'Judul minimal 5 karakter.';
if (strlen($isi) < 20)                           $errors[] = 'Isi laporan minimal 20 karakter.';
if ($id_kategori < 1)                            $errors[] = 'Pilih kategori laporan.';
if ($jenis === 'Pengaduan') {
    if (empty($tgl))    $errors[] = 'Tanggal kejadian wajib diisi.';
    if (empty($lokasi)) $errors[] = 'Lokasi kejadian wajib diisi.';
}

// Handle file upload
$file_path = null;
if ($jenis === 'Pengaduan' && isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file    = $_FILES['lampiran'];
    $allowed = ['jpg','jpeg','png','pdf'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat upload.';
    } elseif (!in_array($ext, $allowed)) {
        $errors[] = 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Ukuran file maksimal 5MB.';
    } else {
        $upload_dir = __DIR__ . '/../../../assets/uploads/laporan/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $filename = uniqid('lpk_', true) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $file_path = 'assets/uploads/laporan/' . $filename;
        } else {
            $errors[] = 'Gagal menyimpan file. Pastikan folder uploads/laporan/ dapat ditulis.';
        }
    }
}

if (!empty($errors)) {
    $_SESSION['laporan_errors'] = $errors;
    $_SESSION['laporan_old'] = [
        'jenis'       => $jenis,
        'judul'       => $judul,
        'isi'         => $isi,
        'id_kategori' => $id_kategori,
        'tgl'         => $tgl,
        'lokasi'      => $lokasi,
    ];
    header('Location: /RPL/pages/user/laporan-form.php');
    exit;
}

function generateNomorLocal(mysqli $conn): string {
    $year = date('Y');
    do {
        $rand  = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $nomor = "LPK-$year-$rand";
        $r = $conn->query("SELECT id_laporan FROM laporan WHERE nomor_laporan='$nomor'");
    } while ($r && $r->num_rows > 0);
    return $nomor;
}

$nomor = generateNomorLocal($conn);
$tgl_save    = ($jenis === 'Pengaduan' && $tgl)    ? $tgl    : null;
$lokasi_save = ($jenis === 'Pengaduan' && $lokasi) ? $lokasi : null;

$stmt = $conn->prepare("INSERT INTO laporan (nomor_laporan, id_user, id_kategori, jenis_laporan, judul, isi_laporan, tanggal_kejadian, lokasi_kejadian, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('siissssss', $nomor, $id_user, $id_kategori, $jenis, $judul, $isi, $tgl_save, $lokasi_save, $file_path);

if ($stmt->execute()) {
    $_SESSION['laporan_sukses'] = "Laporan <strong>$nomor</strong> berhasil dikirim!";
    header('Location: /RPL/pages/user/status-laporan.php');
    exit;
} else {
    $_SESSION['laporan_errors'] = ['Gagal menyimpan laporan. Coba lagi.'];
    $_SESSION['laporan_old'] = [
        'jenis'       => $jenis,
        'judul'       => $judul,
        'isi'         => $isi,
        'id_kategori' => $id_kategori,
        'tgl'         => $tgl,
        'lokasi'      => $lokasi,
    ];
    header('Location: /RPL/pages/user/laporan-form.php');
    exit;
}
