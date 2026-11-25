<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Pendaftaran;
use pbo_FP\Models\Event;

session_start();

// Cek apakah user sudah login dan benar-benar mahasiswa
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$halaman = 'dashboard';
$daftarStatus = Pendaftaran::untukMahasiswa($_SESSION['id_user']);
$semuaEvent   = Event::semua();

// Hitung ringkasan status pendaftaran dari database
$totalPendaftaran = count($daftarStatus);
$jumlahMenunggu   = 0;
$jumlahDiterima   = 0;
$jumlahDitolak    = 0;

foreach ($daftarStatus as $s) {
    if ($s['status_pendaftaran'] === 'menunggu') $jumlahMenunggu++;
    elseif ($s['status_pendaftaran'] === 'diterima') $jumlahDiterima++;
    elseif ($s['status_pendaftaran'] === 'ditolak') $jumlahDitolak++;
}

// Ambil maksimal 3 pendaftaran terbaru & 3 event terbaru untuk ditampilkan di dashboard
$pendaftaranTerbaru = array_slice($daftarStatus, 0, 3);
$eventTerbaru       = array_slice($semuaEvent, 0, 3);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="halaman-dashboard">
        <section class="isi-dashboard">

            <div class="kontainer-sambutan">
                <div class="sambutan-teks">
                    <h2>Halo, <span id="namaUser"><?= htmlspecialchars($_SESSION['nama_user']) ?></span></h2>
                    <p>
                        Selamat datang di sistem rekrutmen kepanitiaan.
                        Jangan lewatkan kesempatan untuk menjadi bagian
                        dari event-event menarik di kampus.
                    </p>
                </div>
                <div class="sambutan-gambar">
                    <img src="../images/banner.png" alt="Kegiatan kepanitiaan">
                </div>
            </div>

            <div class="ringkasan">
                <div class="ringkas-item">
                    <strong><?= $totalPendaftaran ?></strong>
                    <span>Total pendaftaran</span>
                </div>
                <div class="ringkas-item">
                    <strong class="warna-menunggu"><?= $jumlahMenunggu ?></strong>
                    <span>Menunggu</span>
                </div>
                <div class="ringkas-item">
                    <strong class="warna-diterima"><?= $jumlahDiterima ?></strong>
                    <span>Diterima</span>
                </div>
                <div class="ringkas-item">
                    <strong class="warna-ditolak"><?= $jumlahDitolak ?></strong>
                    <span>Ditolak</span>
                </div>
            </div>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Event yang sedang buka</h2>
                    <a href="events.php">Lihat semua event</a>
                </div>

                <div class="panel-dash">
                    <?php if (empty($eventTerbaru)): ?>
                        <p style="padding: 16px; color: #64748b;">Belum ada event tersedia saat ini.</p>
                    <?php else: ?>
                        <?php foreach ($eventTerbaru as $e): ?>
                            <?php 
                                $namaEv = $e->getNamaEvent();
                                $inisial = strtoupper(substr($namaEv, 0, 2));
                            ?>
                            <div class="baris-event">
                                <div class="logo-mini"><?= htmlspecialchars($inisial) ?></div>
                                <div class="baris-info">
                                    <strong><?= htmlspecialchars($namaEv) ?></strong>
                                    <small><?= htmlspecialchars($e->getStatusEvent()) ?></small>
                                </div>
                                <span class="batas"><?= htmlspecialchars($e->getTanggalEvent()) ?></span>
                                <a href="detail_event.php?id=<?= (int) $e->getIdEvent() ?>" class="btn-mini">Daftar</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Pendaftaran terbaru</h2>
                    <a href="pendaftaran.php">Lihat semua pendaftaran</a>
                </div>

                <div class="panel-dash">
                    <?php if (empty($pendaftaranTerbaru)): ?>
                        <p style="padding: 16px; color: #64748b;">Kamu belum pernah mendaftar ke kepanitiaan mana pun.</p>
                    <?php else: ?>
                        <table class="tabel-mini">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Seksi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendaftaranTerbaru as $s): ?>
                                    <?php 
                                        $st = strtolower($s['status_pendaftaran']);
                                        $kelasWarna = 'warna-' . $st; // warna-menunggu, warna-diterima, warna-ditolak
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['nama_event']) ?></td>
                                        <td><?= htmlspecialchars($s['nama_divisi']) ?></td>
                                        <td>
                                            <span class="status-mini <?= htmlspecialchars($kelasWarna) ?>">
                                                <?= htmlspecialchars(ucfirst($st)) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>