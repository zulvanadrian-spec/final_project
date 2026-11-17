<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Pendaftaran;

session_start();

// Cek apakah user sudah login dan benar-benar mahasiswa
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$daftarStatus = Pendaftaran::untukMahasiswa($_SESSION['id_user']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mahasiswa</title>
</head>
<body>
    <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama_user']) ?>!</h1>
    <p>Ini adalah halaman Dashboard khusus Mahasiswa.</p>

    <h3>Status Pendaftaran Saya</h3>

<?php if (empty($daftarStatus)) { ?>
    <p>Belum ada pendaftaran.</p>
    <?php } else { ?>
    <table border="1" cellpadding="6">
        <tr><th>Event</th><th>Divisi</th><th>Tanggal Daftar</th><th>Status</th><th>Catatan</th></tr>
        <?php foreach ($daftarStatus as $s) { ?>
        <tr>
            <td><?= htmlspecialchars($s['nama_event']) ?></td>
            <td><?= htmlspecialchars($s['nama_divisi']) ?></td>
            <td><?= htmlspecialchars($s['tanggal_pendaftaran']) ?></td>
            <td><?= htmlspecialchars($s['status_pendaftaran']) ?></td>
            <td><?= htmlspecialchars($s['catatan'] ?? '-') ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
    
    <br><a href="events.php">Lihat Event</a>
    <br><a href="../proses/proses_login.php">Logout</a>
</body>
</html>