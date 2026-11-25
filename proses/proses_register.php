<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\User;

session_start();

function kembali($tujuan, $kunci, $pesan) {
    $_SESSION[$kunci] = $pesan;
    header("Location: ../pages/$tujuan");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/login.php");
    exit;
}

$nama_user  = trim($_POST['nama'] ?? '');
$email      = trim($_POST['email'] ?? '');
$nim        = trim($_POST['nim'] ?? '');
$password   = $_POST['password'] ?? '';
$konfirmasi = $_POST['konfirmasi'] ?? '';
$role       = $_POST['peran'] ?? 'mahasiswa';

if (!in_array($role, ['mahasiswa', 'rekruter'], true)) {
    kembali('register.php', 'error', 'Pilihan peran tidak valid.');
}

if ($nama_user === '' || $email === '' || $password === '' || ($role === 'mahasiswa' && $nim === '')) {
    kembali('register.php', 'error', 'Semua field wajib diisi.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    kembali('register.php', 'error', 'Format email tidak valid.');
}

if (strlen($password) < 8) {
    kembali('register.php', 'error', 'Password minimal 8 karakter.');
}

if ($konfirmasi !== '' && $password !== $konfirmasi) {
    kembali('register.php', 'error', 'Konfirmasi password tidak cocok.');
}

// Validasi & upload berkas SK khusus rekruter
$namaFileBerkas = null;
if ($role === 'rekruter') {
    if (!isset($_FILES['berkas']) || $_FILES['berkas']['error'] !== UPLOAD_ERR_OK) {
        kembali('register.php', 'error', 'Berkas SK atau surat tugas wajib diunggah untuk rekruter.');
    }

    $file = $_FILES['berkas'];
    if ($file['size'] > 2 * 1024 * 1024) {
        kembali('register.php', 'error', 'Ukuran berkas maksimal 2 MB.');
    }

    $ekstensiValid = ['png', 'jpg', 'jpeg', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $ekstensiValid, true)) {
        kembali('register.php', 'error', 'Format berkas harus PNG, JPG, atau PDF.');
    }

    $folderUpload = __DIR__ . '/../uploads/';
    if (!is_dir($folderUpload)) {
        mkdir($folderUpload, 0777, true);
    }

    $namaFileBerkas = 'sk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $folderUpload . $namaFileBerkas)) {
        kembali('register.php', 'error', 'Gagal mengunggah berkas SK.');
    }
}

$db = Database::getConnection();

if ($role === 'mahasiswa') {
    $stmt = $db->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim = ?");
    $stmt->execute([$nim]);
    if ($stmt->fetch()) {
        kembali('register.php', 'error', 'NIM sudah terdaftar.');
    }
}

$stmt = $db->prepare("SELECT id_user FROM user WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    kembali('register.php', 'error', 'Email sudah terdaftar.');
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO user (nama_user, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$nama_user, $email, User::hashPassword($password)]);
    $id_user = $db->lastInsertId();

    if ($role === 'mahasiswa') {
        $stmt = $db->prepare("INSERT INTO mahasiswa (id_user, nim) VALUES (?, ?)");
        $stmt->execute([$id_user, $nim]);
    } else {
        $stmt = $db->prepare("INSERT INTO pengajuan_rekruter (id_user, berkas) VALUES (?, ?)");
        $stmt->execute([$id_user, $namaFileBerkas]);
    }

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    if ($namaFileBerkas && file_exists(__DIR__ . '/../uploads/' . $namaFileBerkas)) {
        unlink(__DIR__ . '/../uploads/' . $namaFileBerkas);
    }
    kembali('register.php', 'error', 'Pendaftaran gagal, coba lagi.');
}

$pesan = $role === 'rekruter'
    ? 'Pengajuan akun rekruter terkirim. Tunggu persetujuan admin sebelum login.'
    : 'Akun berhasil dibuat, silakan login.';
kembali('login.php', 'sukses', $pesan);