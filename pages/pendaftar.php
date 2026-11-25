<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Pendaftaran;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'rekruter') {
    header("Location: login.php");
    exit;
}

$halaman = 'pendaftar';
$daftar = Pendaftaran::untukRekruter($_SESSION['id_user']);

// Kumpulkan daftar unik Event & Divisi untuk dropdown filter
$listEvent = [];
$listDivisi = [];
foreach ($daftar as $row) {
    $listEvent[$row['nama_event']] = true;
    $listDivisi[$row['nama_divisi']] = true;
}

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftar - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/pendaftar.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar-rekruter.php'; ?>

    <main class="halaman-pendaftaran halaman-pendaftar">
        <section class="isi-dashboard">

            <?php if ($sukses): ?>
                <div style="background: #dcfce7; color: #15803d; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                    <?= htmlspecialchars($sukses) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="filter-pendaftar">
                <input type="search" id="cari" placeholder="Cari nama atau NIM">

                <select id="f-event">
                    <option value="">Semua event</option>
                    <?php foreach (array_keys($listEvent) as $namaEv): ?>
                        <option value="<?= htmlspecialchars($namaEv) ?>"><?= htmlspecialchars($namaEv) ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="f-divisi">
                    <option value="">Semua divisi</option>
                    <?php foreach (array_keys($listDivisi) as $namaDiv): ?>
                        <option value="<?= htmlspecialchars($namaDiv) ?>"><?= htmlspecialchars($namaDiv) ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="f-status">
                    <option value="">Semua status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>

            <p class="jumlah" id="jumlah"></p>

            <div class="tabel-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Event</th>
                            <th>Divisi</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isi-tabel">
                        <?php if (empty($daftar)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 24px; color: #64748b;">
                                    Belum ada pendaftar untuk event yang kamu kelola.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftar as $p): ?>
                                <?php
                                    $idPend = (int) $p['id_pendaftaran'];
                                    $status = $p['status_pendaftaran'];
                                ?>
                                <tr class="<?= $status === 'ditolak' ? 'ditolak' : '' ?>"
                                    data-event="<?= htmlspecialchars($p['nama_event']) ?>"
                                    data-divisi="<?= htmlspecialchars($p['nama_divisi']) ?>"
                                    data-status="<?= htmlspecialchars($status) ?>">
                                    <td>
                                        <span class="utama"><?= htmlspecialchars($p['nama_user']) ?></span>
                                        <span class="sub">NIM <?= htmlspecialchars($p['nim']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($p['nama_event']) ?></td>
                                    <td><?= htmlspecialchars($p['nama_divisi']) ?></td>
                                    <td><?= htmlspecialchars($p['tanggal_pendaftaran'] ?? '-') ?></td>
                                    <td>
                                        <span class="status <?= htmlspecialchars($status) ?>">
                                            <?= ucfirst(htmlspecialchars($status)) ?>
                                        </span>
                                    </td>
                                    <td class="sel-aksi">
                                        <?php if ($status === 'menunggu'): ?>
                                            <a href="periksa_pendaftaran.php?id=<?= $idPend ?>" class="aksi aksi-periksa">Periksa</a>
                                        <?php else: ?>
                                            <a href="periksa_pendaftaran.php?id=<?= $idPend ?>" class="aksi aksi-detail">Detail</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="kosong" id="kosong" hidden>Tidak ada pendaftar yang cocok dengan filter.</p>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
    <script src="../js/pendaftar.js"></script>
</body>
</html>