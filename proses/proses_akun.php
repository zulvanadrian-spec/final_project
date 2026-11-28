<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
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

    $db = Database::getConnection();

    // Ambil nama file berkas sebelum status diputuskan
    $berkasLama = null;
    if ($keputusan === 'ditolak') {
        $stmtBerkas = $db->prepare("SELECT berkas FROM pengajuan_rekruter WHERE id_pengajuan = ?");
        $stmtBerkas->execute([$id_pengajuan]);
        $berkasLama = $stmtBerkas->fetchColumn();
    }

    $catatanSimpan = $catatan !== '' ? $catatan : null;
    $calon = Admin::putuskanPengajuan($id_pengajuan, $keputusan, $catatanSimpan);

    if ($calon) {
        // Hapus file fisik di folder uploads/ & kosongkan kolom berkas jika ditolak
        if ($keputusan === 'ditolak' && !empty($berkasLama)) {
            $pathFile = __DIR__ . '/../uploads/' . basename($berkasLama);
            if (is_file($pathFile)) {
                @unlink($pathFile);
            }

            $stmtKosongkan = $db->prepare("UPDATE pengajuan_rekruter SET berkas = NULL WHERE id_pengajuan = ?");
            $stmtKosongkan->execute([$id_pengajuan]);
        }

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