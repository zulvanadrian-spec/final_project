<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\User;

session_start();

// helper kecil: set pesan, pindah halaman, stop
function kembali($tujuan, $kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/$tujuan");
    exit;
}

// (1) hanya terima request POST (bukan dibuka langsung lewat URL)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/register.php");
    exit;
}

// (2) ambil data dari form
$nama_user = trim($_POST['nama_user'] ?? '');
$email     = trim($_POST['email'] ?? '');
$nim       = trim($_POST['nim'] ?? '');
$password  = $_POST['password'] ?? '';

// (3) validasi
if ($nama_user === '' || $email === '' || $nim === '' || $password === '') {
    kembali('register.php', 'error', 'Semua field wajib diisi.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    kembali('register.php', 'error', 'Format email tidak valid.');
}

if (strlen($password) < 6) {
    kembali('register.php', 'error', 'Password minimal 6 karakter.');
}

$db = Database::getConnection();

// (4) cek email dan nim belum kepakai
$stmt = $db->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim = ?");
$stmt->execute([$nim]);

if ($stmt->fetch()) {
    kembali('register.php', 'error', 'NIM sudah terdaftar.');
}

$stmt = $db->prepare("SELECT id_user FROM user WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    kembali('register.php', 'error', 'Email sudah terdaftar.');
}

// (5) simpan ke 2 tabel sekaligus pakai transaksi
try {
    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO user (nama_user, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$nama_user, $email, User::hashPassword($password)]);

    $id_user = $db->lastInsertId();

    $stmt = $db->prepare("INSERT INTO mahasiswa (id_user, nim) VALUES (?, ?)");
    $stmt->execute([$id_user, $nim]);

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    kembali('register.php', 'error', 'Pendaftaran gagal, coba lagi.');
}

// (6) sukses -> ke halaman login
kembali('login.php', 'sukses', 'Akun berhasil dibuat, silakan login.');