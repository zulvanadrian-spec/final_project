<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Pendaftaran;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$halaman = 'pendaftaran';
$daftarStatus = Pendaftaran::untukMahasiswa($_SESSION['id_user']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Saya - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="halaman-pendaftaran">
        <header class="header-dashboard">

        </header>

        <section class="isi-dashboard">
            <div class="tabel-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Divisi</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftarStatus)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 24px; color: #64748b;">
                                    Kamu belum mendaftar ke event mana pun. <a href="events.php" style="text-decoration: underline;">Cari event sekarang</a>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftarStatus as $s): ?>
                                <?php
                                    $st = strtolower($s['status_pendaftaran']);
                                    $catatan = trim($s['catatan'] ?? '');
                                ?>
                                <tr class="<?= $st === 'ditolak' ? 'ditolak' : '' ?>">
                                    <td>
                                        <span class="utama"><?= htmlspecialchars($s['nama_event']) ?></span>
                                        <?php if (!empty($s['tanggal_event'])): ?>
                                            <span class="sub">Pelaksanaan <?= htmlspecialchars($s['tanggal_event']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['nama_divisi']) ?></td>
                                    <td><?= htmlspecialchars($s['tanggal_pendaftaran']) ?></td>
                                    <td><span class="status <?= htmlspecialchars($st) ?>"><?= htmlspecialchars(ucfirst($st)) ?></span></td>
                                    <td class="catatan <?= $catatan === '' ? 'kosong-catatan' : '' ?>">
                                        <?= $catatan !== '' ? htmlspecialchars($catatan) : 'Belum ada catatan' ?>
                                    </td>
                                    <td class="sel-aksi">
                                        <?php if (!empty($s['id_event'])): ?>
                                            <a href="detail_event.php?id=<?= (int) $s['id_event'] ?>" class="aksi aksi-detail">Detail</a>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>

</html>