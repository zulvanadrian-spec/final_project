<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;

session_start();

function kembali($kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/profil.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../pages/login.php");
    exit;
}

$nama     = trim($_POST['nama'] ?? '');
$lahir    = trim($_POST['lahir'] ?? '');
$gender   = trim($_POST['gender'] ?? '');
$hp       = trim($_POST['hp'] ?? '');
$email    = trim($_POST['email'] ?? '');
$nim      = trim($_POST['nim'] ?? '');
$prodi    = trim($_POST['prodi'] ?? '');
$angkatan = trim($_POST['angkatan'] ?? '');

if ($nama === '' || $email === '' || $nim === '') {
    kembali('error', 'Nama, email, dan NIM wajib diisi.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    kembali('error', 'Format email tidak valid.');
}

if ($gender !== '' && !in_array($gender, ['laki-laki', 'perempuan'], true)) {
    kembali('error', 'Jenis kelamin tidak valid.');
}

$db = Database::getConnection();
$id_user = $_SESSION['id_user'];

// Cek apakah email dipakai user lain
$stmt = $db->prepare("SELECT id_user FROM user WHERE email = ? AND id_user <> ?");
$stmt->execute([$email, $id_user]);
if ($stmt->fetch()) {
    kembali('error', 'Email sudah digunakan oleh akun lain.');
}

// Cek apakah NIM dipakai mahasiswa lain
$stmt = $db->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim = ? AND id_user <> ?");
$stmt->execute([$nim, $id_user]);
if ($stmt->fetch()) {
    kembali('error', 'NIM sudah digunakan oleh mahasiswa lain.');
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE user SET nama_user = ?, email = ? WHERE id_user = ?");
    $stmt->execute([$nama, $email, $id_user]);

    $stmt = $db->prepare("
        UPDATE mahasiswa 
        SET nim = ?, tanggal_lahir = ?, jenis_kelamin = ?, no_hp = ?, prodi = ?, angkatan = ? 
        WHERE id_user = ?
    ");
    $stmt->execute([
        $nim,
        $lahir !== '' ? $lahir : null,
        $gender !== '' ? $gender : null,
        $hp !== '' ? $hp : null,
        $prodi !== '' ? $prodi : null,
        $angkatan !== '' ? $angkatan : null,
        $id_user
    ]);

    $db->commit();
    $_SESSION['nama_user'] = $nama;
    kembali('sukses', 'Profil berhasil diperbarui.');
} catch (\Exception $e) {
    $db->rollBack();
    kembali('error', 'Gagal memperbarui profil.');
}