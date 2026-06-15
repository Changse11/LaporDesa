<?php
// proses admin: aksi pada laporan (terima, tolak, selesai)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../lib/koneksi.php';
require_once __DIR__ . '/../../../lib/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /RPL/pages/auth/masuk.php');
    exit;
}
$admin = $_SESSION['user'];

// id bisa dari GET atau POST
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    header('Location: /RPL/pages/admin/laporan.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /RPL/pages/admin/detail-laporan-belum.php?id=' . $id);
    exit;
}

$aksi      = $_POST['aksi'] ?? '';
$komentar  = trim($_POST['komentar'] ?? '');
$prioritas = $_POST['prioritas'] ?? null;

// -----------------------------------------------
// Ambil data laporan + email + nama user
// -----------------------------------------------
$stmt_lap = $conn->prepare("
    SELECT l.*, u.nama_lengkap, u.email, k.nama_kategori
    FROM laporan l
    JOIN users u ON l.id_user = u.id_user
    JOIN kategori k ON l.id_kategori = k.id_kategori
    WHERE l.id_laporan = ?
");
$stmt_lap->bind_param('i', $id);
$stmt_lap->execute();
$lap = $stmt_lap->get_result()->fetch_assoc();

// -----------------------------------------------
// Fungsi kirim email notifikasi
// -----------------------------------------------
function kirimNotifikasiEmail($email_user, $nama_user, $lap, $status_baru, $komentar_admin = '') {
    $config = [
        'diproses' => [
            'label'    => 'Sedang Diproses',
            'warna'    => '#1a5c2a',
            'icon'     => '⚙️',
            'pesan'    => 'Laporan Anda telah diterima dan sedang ditindaklanjuti oleh tim kami.',
            'badge_bg' => '#dcfce7',
            'badge_fg' => '#15803d',
        ],
        'ditolak' => [
            'label'    => 'Ditolak',
            'warna'    => '#b91c1c',
            'icon'     => '❌',
            'pesan'    => 'Mohon maaf, laporan Anda tidak dapat kami tindaklanjuti saat ini.',
            'badge_bg' => '#fee2e2',
            'badge_fg' => '#b91c1c',
        ],
        'selesai' => [
            'label'    => 'Selesai',
            'warna'    => '#1d4ed8',
            'icon'     => '✅',
            'pesan'    => 'Laporan Anda telah selesai ditangani. Terima kasih atas partisipasi Anda.',
            'badge_bg' => '#dbeafe',
            'badge_fg' => '#1d4ed8',
        ],
    ];

    $c        = $config[$status_baru];
    $tahun    = date('Y');
    $tgl_kini = date('d F Y, H:i') . ' WIB';
    $nomor    = htmlspecialchars($lap['nomor_laporan']);
    $judul    = htmlspecialchars($lap['judul']);
    $kategori = htmlspecialchars($lap['nama_kategori']);
    $jenis    = htmlspecialchars($lap['jenis_laporan']);

    $blok_komentar = '';
    if ($komentar_admin !== '') {
        $blok_komentar = "
        <tr>
          <td style='padding:0 40px 24px;'>
            <div style='background:#f8fafc;border-left:4px solid {$c['warna']};border-radius:4px;padding:14px 16px;'>
              <p style='margin:0 0 4px;font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;'>Catatan Admin</p>
              <p style='margin:0;font-size:14px;color:#374151;line-height:1.6;'>" . nl2br(htmlspecialchars($komentar_admin)) . "</p>
            </div>
          </td>
        </tr>";
    }

    $html_body = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"/></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:40px 0;">
  <tr><td align="center">
  <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

    <!-- Header -->
    <tr>
      <td style="background:#1a5c2a;border-radius:12px 12px 0 0;padding:28px 40px;text-align:center;">
        <span style="color:#fff;font-size:20px;font-weight:700;">🏠 LaporKades</span>
        <p style="color:#a8d5b5;font-size:12px;margin:6px 0 0;">Sistem Administrasi Warga Terpadu</p>
      </td>
    </tr>

    <!-- Status Banner -->
    <tr>
      <td style="background:{$c['warna']};padding:14px 40px;text-align:center;">
        <span style="color:#fff;font-size:14px;font-weight:600;">{$c['icon']} Status Laporan: {$c['label']}</span>
      </td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="background:#fff;padding:36px 40px 24px;">
        <p style="font-size:15px;color:#374151;margin:0 0 6px;">Halo, <strong>{$nama_user}</strong></p>
        <p style="font-size:15px;color:#374151;line-height:1.6;margin:0 0 24px;">{$c['pesan']}</p>

        <!-- Info Laporan -->
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:24px;">
          <tr><td style="padding:16px 20px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="font-size:12px;color:#6b7280;padding-bottom:10px;width:40%;">Nomor Laporan</td>
                <td style="font-size:13px;color:#111827;font-weight:600;padding-bottom:10px;">{$nomor}</td>
              </tr>
              <tr>
                <td style="font-size:12px;color:#6b7280;padding-bottom:10px;">Judul</td>
                <td style="font-size:13px;color:#111827;padding-bottom:10px;">{$judul}</td>
              </tr>
              <tr>
                <td style="font-size:12px;color:#6b7280;padding-bottom:10px;">Jenis</td>
                <td style="font-size:13px;color:#111827;padding-bottom:10px;">{$jenis}</td>
              </tr>
              <tr>
                <td style="font-size:12px;color:#6b7280;padding-bottom:10px;">Kategori</td>
                <td style="font-size:13px;color:#111827;padding-bottom:10px;">{$kategori}</td>
              </tr>
              <tr>
                <td style="font-size:12px;color:#6b7280;">Status Terbaru</td>
                <td><span style="background:{$c['badge_bg']};color:{$c['badge_fg']};font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;">{$c['label']}</span></td>
              </tr>
            </table>
          </td></tr>
        </table>
      </td>
    </tr>

    <!-- Catatan Admin (jika ada) -->
    {$blok_komentar}

    <!-- Waktu update -->
    <tr>
      <td style="background:#fff;padding:0 40px 32px;">
        <p style="font-size:13px;color:#6b7280;line-height:1.6;margin:0;">
          Diperbarui pada <strong>{$tgl_kini}</strong>.<br>
          Pantau perkembangan laporan melalui akun LaporKades Anda.
        </p>
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="background:#f9fafb;border-top:1px solid #e5e7eb;border-radius:0 0 12px 12px;padding:18px 40px;text-align:center;">
        <p style="margin:0 0 2px;font-size:12px;color:#9ca3af;">&copy; {$tahun} LaporKades &mdash; Sistem Administrasi Warga Terpadu</p>
        <p style="margin:0;font-size:12px;color:#d1d5db;">Email ini dikirim otomatis, harap tidak membalas.</p>
      </td>
    </tr>

  </table>
  </td></tr>
</table>
</body>
</html>
HTML;

    $plain_body = "Halo {$nama_user},\n\n{$c['pesan']}\n\nNomor Laporan : {$nomor}\nJudul         : {$judul}\nKategori      : {$kategori}\nStatus        : {$c['label']}\nDiperbarui    : {$tgl_kini}\n"
        . ($komentar_admin ? "\nCatatan Admin :\n{$komentar_admin}\n" : "")
        . "\n© {$tahun} LaporKades";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lapordesakutapohaci@gmail.com';
        $mail->Password   = 'vwnh maee gszn wbou';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('lapordesakutapohaci@gmail.com', 'LaporKades');
        $mail->addAddress($email_user, $nama_user);

        $mail->isHTML(true);
        $mail->Subject = "📋 Update Laporan #{$nomor} – {$c['label']} | LaporKades";
        $mail->Body    = $html_body;
        $mail->AltBody = $plain_body;

        $mail->send();
    } catch (Exception $e) {
        error_log('PHPMailer Notifikasi Error: ' . $mail->ErrorInfo);
        // Proses tetap lanjut meski email gagal
    }
}

// -----------------------------------------------
// Proses aksi
// -----------------------------------------------
if ($aksi === 'terima') {
    $stmt = $conn->prepare("UPDATE laporan SET status_laporan='diproses', prioritas=?, diproses_oleh=?, diproses_pada=NOW(), updated_at=NOW() WHERE id_laporan=?");
    $stmt->bind_param('sii', $prioritas, $admin['id_user'], $id);
    $stmt->execute();
    if ($komentar !== '') {
        $stmt2 = $conn->prepare("INSERT INTO komentar (id_laporan, id_user, isi_komentar, tanggal) VALUES (?, ?, ?, NOW())");
        $stmt2->bind_param('iis', $id, $admin['id_user'], $komentar);
        $stmt2->execute();
    }
    if ($lap) kirimNotifikasiEmail($lap['email'], $lap['nama_lengkap'], $lap, 'diproses', $komentar);
    $_SESSION['flash'] = "Laporan berhasil diterima dan diproses. Notifikasi dikirim ke pelapor.";
    header('Location: /RPL/pages/admin/laporan.php?tab=sudah');
    exit;
}

if ($aksi === 'tolak') {
    $stmt = $conn->prepare("UPDATE laporan SET status_laporan='ditolak', diproses_oleh=?, diproses_pada=NOW(), updated_at=NOW() WHERE id_laporan=?");
    $stmt->bind_param('ii', $admin['id_user'], $id);
    $stmt->execute();
    if ($komentar !== '') {
        $stmt2 = $conn->prepare("INSERT INTO komentar (id_laporan, id_user, isi_komentar, tanggal) VALUES (?, ?, ?, NOW())");
        $stmt2->bind_param('iis', $id, $admin['id_user'], $komentar);
        $stmt2->execute();
    }
    if ($lap) kirimNotifikasiEmail($lap['email'], $lap['nama_lengkap'], $lap, 'ditolak', $komentar);
    $_SESSION['flash'] = "Laporan telah ditolak. Notifikasi dikirim ke pelapor.";
    header('Location: /RPL/pages/admin/laporan.php');
    exit;
}

if ($aksi === 'selesai') {
    $komentar_baru = $komentar;
    $stmt2 = $conn->prepare("UPDATE laporan SET status_laporan='selesai', selesai_oleh=?, selesai_pada=NOW(), updated_at=NOW() WHERE id_laporan=?");
    $stmt2->bind_param('ii', $admin['id_user'], $id);
    $stmt2->execute();
    if ($komentar_baru !== '') {
        $stmt3 = $conn->prepare("INSERT INTO komentar (id_laporan, id_user, isi_komentar, tanggal) VALUES (?, ?, ?, NOW())");
        $stmt3->bind_param('iis', $id, $admin['id_user'], $komentar_baru);
        $stmt3->execute();
    }
    if ($lap) kirimNotifikasiEmail($lap['email'], $lap['nama_lengkap'], $lap, 'selesai', $komentar_baru);
    $_SESSION['flash'] = "Laporan berhasil ditandai sebagai selesai. Notifikasi dikirim ke pelapor.";
    header('Location: /RPL/pages/admin/histori.php');
    exit;
}

// Jika aksi tidak dikenal
header('Location: /RPL/pages/admin/laporan.php');
exit;