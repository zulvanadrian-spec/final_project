<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\User;

session_start();

// Fungsi helper buat ngembaliin user ke halaman login kalau ada error
function kembali($tujuan, $kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/$tujuan");
    exit;
}

// 1. Pastikan cuma bisa diakses lewat form (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/login.php");
    exit;
}

// 2. Tangkap inputan form
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    kembali('login.php', 'error', 'Email dan password wajib diisi.');
}

// 3. Buka koneksi database
$db = Database::getConnection();

// 4. Cari data user di tabel `user` berdasarkan email
$stmt = $db->prepare("SELECT * FROM user WHERE email = ?");
$stmt->execute([$email]);
$userData = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$userData) {
    kembali('login.php', 'error', 'Email tidak terdaftar.');
}

// 5. Bikin objek User buat pakai method verifikasi password lu
$user = new User(
    $userData['id_user'], 
    $userData['nama_user'], 
    $userData['email'], 
    $userData['password']
);

if (!$user->verifyPassword($password)) {
    kembali('login.php', 'error', 'Password salah.');
}

// 6. Cek Role (Mahasiswa, Admin, atau Rekruter) dengan ngecek ke tabel masing-masing
$role = '';

// Cek di tabel mahasiswa
$stmtMhs = $db->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE id_user = ?");
$stmtMhs->execute([$userData['id_user']]);
if ($stmtMhs->fetch()) {
    $role = 'mahasiswa';
}

// Cek di tabel admin (kalau bukan mahasiswa)
if ($role === '') {
    $stmtAdmin = $db->prepare("SELECT id_admin FROM admin WHERE id_user = ?");
    $stmtAdmin->execute([$userData['id_user']]);
    if ($stmtAdmin->fetch()) {
        $role = 'admin';
    }
}

// Cek di tabel rekruter (kalau bukan admin & mahasiswa)
if ($role === '') {
    $stmtRekruter = $db->prepare("SELECT id_rekruter FROM rekruter WHERE id_user = ?");
    $stmtRekruter->execute([$userData['id_user']]);
    if ($stmtRekruter->fetch()) {
        $role = 'rekruter';
    }
}

// 7. Simpan data penting ke Session (biar sistem ingat siapa yang login)
$_SESSION['id_user']   = $userData['id_user'];
$_SESSION['nama_user'] = $userData['nama_user'];
$_SESSION['role']      = $role;

// 8. Redirect sesuai role ke dashboard masing-masing
if ($role === 'mahasiswa') {
    header("Location: ../pages/dashboard_mahasiswa.php");
} elseif ($role === 'admin') {
    header("Location: ../pages/dashboard_admin.php");
} elseif ($role === 'rekruter') {
    header("Location: ../pages/dashboard_rekruter.php");
} else {
    // Jaga-jaga kalau akunnya ngambang (ada di tabel user tapi gak ada di 3 role itu)
    kembali('login.php', 'error', 'Akun tidak memiliki role yang valid.');
}
exit;