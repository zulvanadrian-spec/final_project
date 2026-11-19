<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;

session_start();

function kembali($kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/pendaftar.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['id_user'])
    || ($_SESSION['role'] ?? '') !== 'rekruter') {
    header("Location: ../pages/login.php");
    exit;
}

$id_pendaftaran = (int) ($_POST['id_pendaftaran'] ?? 0);
$keputusan      = $_POST['keputusan'] ?? '';
$catatan        = trim($_POST['catatan'] ?? '');

if (!in_array($keputusan, ['diterima', 'ditolak'], true)) {
    kembali('error', 'Keputusan tidak valid.');
}

$db = Database::getConnection();

// (1) id_rekruter dari akun yang login
$stmt = $db->prepare("SELECT id_rekruter FROM rekruter WHERE id_user = ?");
$stmt->execute([$_SESSION['id_user']]);
$id_rekruter = $stmt->fetchColumn();

// (2) pendaftaran harus masih menunggu dan event-nya milik rekruter ini
$stmt = $db->prepare(
    "SELECT p.id_pendaftaran
     FROM pendaftaran p
     JOIN divisi d ON d.id_divisi = p.id_divisi
     JOIN event e ON e.id_event = d.id_event
     WHERE p.id_pendaftaran = ? AND e.id_rekruter = ? AND p.status_pendaftaran = 'menunggu'"
);
$stmt->execute([$id_pendaftaran, $id_rekruter]);

if (!$stmt->fetch()) {
    kembali('error', 'Pendaftaran tidak ditemukan atau sudah diperiksa.');
}

// (3) simpan review dan ubah status, satu paket
try {
    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO review (id_pendaftaran, id_rekruter, keputusan, catatan, waktu_review) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$id_pendaftaran, $id_rekruter, $keputusan, $catatan]);

    $stmt = $db->prepare("UPDATE pendaftaran SET status_pendaftaran = ? WHERE id_pendaftaran = ?");
    $stmt->execute([$keputusan, $id_pendaftaran]);

    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
    kembali('error', 'Gagal menyimpan, coba lagi.');
}

kembali('sukses', 'Pendaftaran berhasil ' . $keputusan . '.');