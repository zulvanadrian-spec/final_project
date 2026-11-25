<?php

namespace pbo_FP\Core;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {
    public static function kabarkanPengajuan($email, $nama, $keputusan, $catatan = null) {
        $cfg = require __DIR__ . '/../config/mail.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['username'];
        $mail->Password   = $cfg['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $cfg['port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($cfg['from'], $cfg['nama']);
        $mail->addAddress($email, $nama);

        $teksCatatan = '';
        if (!empty($catatan)) {
            $label = $keputusan === 'ditolak' ? 'Alasan penolakan' : 'Catatan admin';
            $teksCatatan = "\n\n{$label}:\n{$catatan}";
        }

        if ($keputusan === 'disetujui') {
            $mail->Subject = 'Pengajuan akun rekruter disetujui';
            $mail->Body    = "Halo $nama,\n\nPengajuan akun rekruter kamu sudah disetujui admin. Silakan login untuk mulai menggunakan akunmu.{$teksCatatan}";
        } else {
            $mail->Subject = 'Pengajuan akun rekruter ditolak';
            $mail->Body    = "Halo $nama,\n\nMaaf, pengajuan akun rekruter kamu ditolak oleh admin.{$teksCatatan}";
        }

        $mail->send();
    }
}