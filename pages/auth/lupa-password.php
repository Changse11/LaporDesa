<?php
include '../lib/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../../lib/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $stmt = $conn->prepare("SELECT id_user, nama_lengkap FROM users WHERE email = ? AND status_akun = 'aktif' LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user  = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));

            // Simpan token di session — berlaku 5 menit (300 detik)
            $_SESSION['reset_token']   = $token;
            $_SESSION['reset_id_user'] = $user['id_user'];
            $_SESSION['reset_expiry']  = time() + 300;

            $nama       = $user['nama_lengkap'];
            $reset_link = "http://localhost/RPL/pages/auth/reset-password.php?token=$token";
            $tahun      = date('Y');
            $waktu_kirim = date('d F Y, H:i') . ' WIB';

            // Template HTML Email
            $html_body = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password – LaporDesa</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

          <!-- Header -->
          <tr>
            <td style="background:#1a5c2a;border-radius:12px 12px 0 0;padding:32px 40px;text-align:center;">
              <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                  <td style="padding-right:12px;vertical-align:middle;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="10" width="18" height="11" rx="1"/>
                      <polyline points="3 10 12 3 21 10"/>
                      <line x1="9" y1="21" x2="9" y2="14"/>
                      <line x1="15" y1="21" x2="15" y2="14"/>
                    </svg>
                  </td>
                  <td style="vertical-align:middle;">
                    <span style="color:#ffffff;font-size:22px;font-weight:700;letter-spacing:0.5px;">LaporDesa</span>
                  </td>
                </tr>
              </table>
              <p style="color:#a8d5b5;font-size:13px;margin:8px 0 0;">Sistem Administrasi Warga Terpadu</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="background:#ffffff;padding:40px 40px 32px;">

              <!-- Icon -->
              <table cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
                <tr>
                  <td style="background:#e8f5ed;border-radius:50%;width:64px;height:64px;text-align:center;vertical-align:middle;padding:16px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1a5c2a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </td>
                </tr>
              </table>

              <h1 style="margin:0 0 8px;font-size:22px;color:#1a1a2e;text-align:center;font-weight:700;">Reset Password Anda</h1>
              <p style="margin:0 0 24px;font-size:14px;color:#6b7280;text-align:center;">Permintaan perubahan kata sandi diterima</p>

              <p style="font-size:15px;color:#374151;margin:0 0 8px;">Halo, <strong>{$nama}</strong></p>
              <p style="font-size:15px;color:#374151;line-height:1.6;margin:0 0 24px;">
                Kami menerima permintaan untuk mereset password akun LaporDesa Anda.
                Klik tombol di bawah ini untuk membuat password baru.
              </p>

              <!-- CTA Button -->
              <table cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
                <tr>
                  <td style="background:#1a5c2a;border-radius:8px;text-align:center;">
                    <a href="{$reset_link}"
                       style="display:inline-block;padding:14px 36px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;letter-spacing:0.3px;">
                      Reset Password Sekarang
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Warning Box -->
              <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
                <tr>
                  <td style="background:#fff8e1;border-left:4px solid #f59e0b;border-radius:4px;padding:14px 16px;">
                    <p style="margin:0;font-size:13px;color:#92400e;">
                      ⏱️ <strong>Link berlaku selama 5 menit</strong> sejak email ini dikirim ({$waktu_kirim}).
                      Segera gunakan sebelum kadaluarsa.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- URL fallback -->
              <p style="font-size:13px;color:#6b7280;margin:0 0 4px;">Atau salin link berikut ke browser Anda:</p>
              <p style="font-size:12px;color:#1a5c2a;word-break:break-all;background:#f0faf4;padding:10px 14px;border-radius:6px;margin:0 0 24px;">
                {$reset_link}
              </p>

              <hr style="border:none;border-top:1px solid #e5e7eb;margin:0 0 20px;" />

              <p style="font-size:13px;color:#9ca3af;line-height:1.6;margin:0;">
                Jika Anda tidak merasa meminta reset password, abaikan email ini.
                Password Anda tidak akan berubah. Jika Anda membutuhkan bantuan,
                hubungi administrator desa setempat.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f9fafb;border-radius:0 0 12px 12px;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
              <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;">
                &copy; {$tahun} LaporDesa &mdash; Sistem Administrasi Warga Terpadu
              </p>
              <p style="margin:0;font-size:12px;color:#d1d5db;">
                Email ini dikirim secara otomatis, harap tidak membalas.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
HTML;

            // Plain text fallback
            $plain_body = "Halo {$nama},\n\nKami menerima permintaan reset password untuk akun LaporDesa Anda.\n\nKlik link berikut untuk reset password (berlaku 5 menit):\n{$reset_link}\n\nJika Anda tidak meminta ini, abaikan email ini.\n\n© {$tahun} LaporDesa";

            // Kirim Email via PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'lapordesakutapohaci@gmail.com';
                $mail->Password   = 'vwnh maee gszn wbou';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('lapordesakutapohaci@gmail.com', 'LaporDesa');
                $mail->addAddress($email, $nama);
                $mail->addReplyTo('no-reply@LaporDesa.id', 'No Reply');

                $mail->isHTML(true);
                $mail->Subject = '🔐 Reset Password – LaporDesa';
                $mail->Body    = $html_body;
                $mail->AltBody = $plain_body;

                $mail->send();
                $success = 'Jika email terdaftar, instruksi reset password telah dikirim. Cek inbox atau folder spam Anda.';

            } catch (Exception $e) {
                // Jangan expose error detail ke user, log saja di server
                error_log('PHPMailer Error: ' . $mail->ErrorInfo);
                $error = 'Gagal mengirim email. Silakan coba beberapa saat lagi.';

                // Bersihkan session jika email gagal kirim
                unset($_SESSION['reset_token'], $_SESSION['reset_id_user'], $_SESSION['reset_expiry']);
            }
        } else {
            // Pesan generic agar email valid/tidak tidak bisa ditebak
            $success = 'Jika email terdaftar, instruksi reset password telah dikirim. Cek inbox atau folder spam Anda.';
        }

        if (isset($stmt)) $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lupa Password – LaporDesa</title>
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
      <h1>Lupa Password</h1>
      <p class="subtitle">
        Masukkan email yang terdaftar. Kami akan mengirimkan link reset password ke email Anda.
      </p>

      <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST">
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Masukkan Email Anda" required />
        </div>
        <button type="submit" class="btn-primary">Kirim Link Reset</button>
      </form>
      <?php endif; ?>

      <p class="footer-link" style="margin-top: 20px;">
        Ingat password? <a href="masuk.php">Kembali Masuk</a>
      </p>
    </div>

  </div>
</body>
</html>