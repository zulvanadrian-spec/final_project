<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Event;

session_start();

function kembali($kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/kelola_event.php");
    exit;
}

function validasiEvent($nama, $tanggal, $status) {
    $tgl = DateTime::createFromFormat('Y-m-d', $tanggal);

    if ($nama === '' || strlen($nama) > 150) return 'Nama event wajib diisi (maksimal 150 karakter).';
    if (!$tgl || $tgl->format('Y-m-d') !== $tanggal) return 'Tanggal tidak valid.';
    if (!in_array($status, ['direncanakan', 'open_recruitment', 'selesai'], true)) return 'Status tidak valid.';

    return null;
    
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['id_user'])
    || ($_SESSION['role'] ?? '') !== 'panitia') {
    header("Location: ../pages/login.php");
    exit;
}

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {
    $nama    = trim($_POST['nama_event'] ?? '');
    $tanggal = $_POST['tanggal_event'] ?? '';
    $status  = $_POST['status_event'] ?? '';

    $tgl = DateTime::createFromFormat('Y-m-d', $tanggal);

    if ($nama === '' || strlen($nama) > 150) {
        kembali('error', 'Nama event wajib diisi (maksimal 150 karakter).');
    }
    if (!$tgl || $tgl->format('Y-m-d') !== $tanggal) {
        kembali('error', 'Tanggal tidak valid.');
    }
    if (!in_array($status, ['direncanakan', 'open_recruitment', 'selesai'], true)) {
        kembali('error', 'Status tidak valid.');
    }

    if (Event::tambahEvent($_SESSION['id_user'], $nama, $tanggal, $status)) {
        kembali('sukses', 'Event berhasil ditambahkan.');
    }
    kembali('error', 'Gagal menyimpan event.');
}

if ($aksi === 'ubah') {
    $id_event = (int) ($_POST['id_event'] ?? 0);
    $nama     = trim($_POST['nama_event'] ?? '');
    $tanggal  = $_POST['tanggal_event'] ?? '';
    $status   = $_POST['status_event'] ?? '';

    $pesan = validasiEvent($nama, $tanggal, $status);
    if ($pesan) {
        kembali('error', $pesan);
    }

    if (Event::ubahEvent($id_event, $_SESSION['id_user'], $nama, $tanggal, $status)) {
        kembali('sukses', 'Event berhasil diubah.');
    }
    kembali('error', 'Event tidak ditemukan.');
}

if ($aksi === 'hapus') {
    $id_event = (int) ($_POST['id_event'] ?? 0);

    try {
        if (Event::hapusEvent($id_event, $_SESSION['id_user'])) {
            kembali('sukses', 'Event berhasil dihapus.');
        }
        kembali('error', 'Event tidak ditemukan.');
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) {
            kembali('error', 'Event tidak bisa dihapus karena sudah ada pendaftar.');
        }
        throw $e;
    }
}
kembali('error', 'Aksi tidak dikenal.');