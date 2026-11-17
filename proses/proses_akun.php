<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Admin;

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

if ($aksi === 'buat_panitia') {
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
        Admin::buatAkunPanitia($nama, $email, $password);
        kembali('sukses', 'Akun panitia berhasil dibuat.');
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) {
            kembali('error', 'Email sudah terdaftar.');
        }
        throw $e;
    }
}

if ($aksi === 'jadikan_panitia') {
    $id_user = (int) ($_POST['id_user'] ?? 0);

    if (Admin::jadikanPanitia($id_user)) {
        kembali('sukses', 'Akun berhasil dijadikan panitia.');
    }
    kembali('error', 'Akun tidak ditemukan atau bukan mahasiswa.');
}

kembali('error', 'Aksi tidak dikenal.');