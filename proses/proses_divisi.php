<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Divisi;

session_start();

function kembali($id_event, $kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/kelola_divisi.php?id=" . (int) $id_event);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['id_user'])
    || ($_SESSION['role'] ?? '') !== 'panitia') {
    header("Location: ../pages/login.php");
    exit;
}

$aksi      = $_POST['aksi'] ?? '';
$id_event  = (int) ($_POST['id_event'] ?? 0);
$id_divisi = (int) ($_POST['id_divisi'] ?? 0);
$nama      = trim($_POST['nama_divisi'] ?? '');
$kuota     = filter_var($_POST['kuota'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);

if ($nama === '' || strlen($nama) > 100) {
    kembali($id_event, 'error', 'Nama divisi wajib diisi (maksimal 100 karakter).');
}
if ($kuota === false) {
    kembali($id_event, 'error', 'Kuota harus berupa angka 1 sampai 1000.');
}

if ($aksi === 'tambah') {
    if (Divisi::tambahDivisi($id_event, $_SESSION['id_user'], $nama, $kuota)) {
        kembali($id_event, 'sukses', 'Divisi berhasil ditambahkan.');
    }
    kembali($id_event, 'error', 'Gagal menambah divisi.');
}

if ($aksi === 'ubah') {
    if (Divisi::ubahDivisi($id_divisi, $_SESSION['id_user'], $nama, $kuota)) {
        kembali($id_event, 'sukses', 'Divisi berhasil diubah.');
    }
    kembali($id_event, 'error', 'Divisi tidak ditemukan.');
}

kembali($id_event, 'error', 'Aksi tidak dikenal.');