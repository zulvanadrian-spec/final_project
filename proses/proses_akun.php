<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Admin;
use pbo_FP\Core\Mailer;

session_start();

function kembali($kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/verifikasi.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['id_user'])
    || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'putuskan_pengajuan') {
    $id_pengajuan = (int) ($_POST['id_pengajuan'] ?? 0);
    $keputusan    = $_POST['keputusan'] ?? '';
    $catatan      = trim($_POST['catatan'] ?? '');

    if (!in_array($keputusan, ['disetujui', 'ditolak'], true)) {
        kembali('error', 'Keputusan tidak valid.');
    }

    if ($keputusan === 'ditolak' && $catatan === '') {
        kembali('error', 'Alasan penolakan wajib diisi.');
    }

    $catatanSimpan = $catatan !== '' ? $catatan : null;
    $calon = Admin::putuskanPengajuan($id_pengajuan, $keputusan, $catatanSimpan);

    if ($calon) {
        $pesan = 'Pengajuan akun ' . $calon['nama_user'] . ' berhasil ' . $keputusan . '.';

        try {
            Mailer::kabarkanPengajuan($calon['email'], $calon['nama_user'], $keputusan, $catatanSimpan);
        } catch (\Throwable $e) {
            // Abaikan jika SMTP lokal belum dikonfigurasi
        }

        kembali('sukses', $pesan);
    }

    kembali('error', 'Pengajuan tidak ditemukan atau sudah diputuskan.');
}

kembali('error', 'Aksi tidak dikenal.');