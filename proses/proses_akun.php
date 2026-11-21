<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Admin;
use pbo_FP\Core\Mailer;

session_start();

function kembali($kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/kelola_akun.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['id_user'])
    || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'buat_rekruter') {
    $nama     = trim($_POST['nama_user'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === '' || strlen($nama) > 100) {
        kembali('error', 'Nama wajib diisi (maksimal 100 karakter).');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        kembali('error', 'Format email tidak valid.');
    }
    if (strlen($password) < 6) {
        kembali('error', 'Password minimal 6 karakter.');
    }

    try {
        Admin::buatAkunRekruter($nama, $email, $password);
        kembali('sukses', 'Akun rekruter berhasil dibuat.');
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) {
            kembali('error', 'Email sudah terdaftar.');
        }
        throw $e;
    }
}

if ($aksi === 'jadikan_rekruter') {
    $id_user = (int) ($_POST['id_user'] ?? 0);

    if (Admin::jadikanRekruter($id_user)) {
        kembali('sukses', 'Akun berhasil dijadikan rekruter.');
    }
    kembali('error', 'Akun tidak ditemukan atau bukan mahasiswa.');
}

if ($aksi === 'putuskan_pengajuan') {
    $id_pengajuan = (int) ($_POST['id_pengajuan'] ?? 0);
    $keputusan    = $_POST['keputusan'] ?? '';

    if (!in_array($keputusan, ['disetujui', 'ditolak'], true)) {
        kembali('error', 'Keputusan tidak valid.');
    }

    $calon = Admin::putuskanPengajuan($id_pengajuan, $keputusan);

if ($calon) {
    $pesan = 'Pengajuan berhasil ' . $keputusan . '.';

    try {
        Mailer::kabarkanPengajuan($calon['email'], $calon['nama_user'], $keputusan);
    } catch (\Throwable $e) {
        $pesan .= ' Email notifikasi gagal terkirim.';
    }

    kembali('sukses', $pesan);
    }
}
kembali('error', 'Aksi tidak dikenal.');