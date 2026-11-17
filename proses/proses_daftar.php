<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;

session_start();

function kembali($id_event, $kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/detail_event.php?id=" . (int) $id_event);
    exit;
}

// hanya mahasiswa yang login, dan harus lewat form (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_SESSION['id_user'])
    || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
    header("Location: ../pages/login.php");
    exit;
}

$id_divisi = (int) ($_POST['id_divisi'] ?? 0);
$id_event  = (int) ($_POST['id_event'] ?? 0);

$db = Database::getConnection();

// (1) cari id_mahasiswa dari akun yang login
$stmt = $db->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?");
$stmt->execute([$_SESSION['id_user']]);
$id_mahasiswa = $stmt->fetchColumn();

// (2) divisi harus ada, ambil kuota dan status event-nya
$stmt = $db->prepare(
    "SELECT d.id_event, d.kuota, e.status_event
     FROM divisi d
     JOIN event e ON e.id_event = d.id_event
     WHERE d.id_divisi = ?"
);
$stmt->execute([$id_divisi]);
$divisi = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$id_mahasiswa || !$divisi) {
    kembali($id_event, 'error', 'Data tidak valid.');
}

if ($divisi['status_event'] !== 'open_recruitment') {
    kembali($divisi['id_event'], 'error', 'Pendaftaran untuk event ini belum dibuka atau sudah ditutup.');
}

// (3) cek kuota (yang ditolak nggak dihitung)
$stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE id_divisi = ? AND status_pendaftaran <> 'ditolak'");
$stmt->execute([$id_divisi]);

if ($stmt->fetchColumn() >= $divisi['kuota']) {
    kembali($divisi['id_event'], 'error', 'Kuota divisi sudah penuh.');
}

// (4) simpan; UNIQUE (id_mahasiswa, id_divisi) bikin daftar dobel ditolak database
try {
    $stmt = $db->prepare("INSERT INTO pendaftaran (id_mahasiswa, id_divisi, tanggal_pendaftaran) VALUES (?, ?, NOW())");
    $stmt->execute([$id_mahasiswa, $id_divisi]);
} catch (\PDOException $e) {
    if ($e->getCode() == 23000) {
        kembali($divisi['id_event'], 'error', 'Kamu sudah mendaftar di divisi ini.');
    }
    throw $e;
}

kembali($divisi['id_event'], 'sukses', 'Pendaftaran berhasil, status: menunggu.');