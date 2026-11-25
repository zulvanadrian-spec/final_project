<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\Event;
use pbo_FP\Models\Pendaftaran;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'rekruter') {
    header("Location: login.php");
    exit;
}

$halaman = 'dashboard';
$id_user = $_SESSION['id_user'];

$eventSaya      = Event::milikRekruter($id_user);
$semuaPendaftar = Pendaftaran::untukRekruter($id_user);

// Hitung ringkasan statistik
$jumlahEventAktif = 0;
foreach ($eventSaya as $ev) {
    if ($ev->getStatusEvent() === 'open_recruitment') {
        $jumlahEventAktif++;
    }
}

$totalPendaftar   = count($semuaPendaftar);
$perluDiperiksa   = [];
$jumlahDiterima   = 0;

foreach ($semuaPendaftar as $p) {
    if ($p['status_pendaftaran'] === 'menunggu') {
        $perluDiperiksa[] = $p;
    } elseif ($p['status_pendaftaran'] === 'diterima') {
        $jumlahDiterima++;
    }
}

$jumlahMenunggu        = count($perluDiperiksa);
$perluDiperiksaTerbaru = array_slice($perluDiperiksa, 0, 5);
$eventSayaTerbaru      = array_slice($eventSaya, 0, 5);

// Hitung jumlah divisi & jumlah pendaftar per event untuk bagian "Event saya"
$db = Database::getConnection();
$stmtDiv = $db->query("SELECT id_event, COUNT(*) FROM divisi GROUP BY id_event");
$jumlahDivisiPerEvent = $stmtDiv->fetchAll(\PDO::FETCH_KEY_PAIR);

$stmtPend = $db->query("
    SELECT d.id_event, COUNT(p.id_pendaftaran) 
    FROM divisi d 
    LEFT JOIN pendaftaran p ON p.id_divisi = d.id_divisi 
    GROUP BY d.id_event
");
$jumlahPendaftarPerEvent = $stmtPend->fetchAll(\PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Rekruter - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/dashboard-rekruter.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar-rekruter.php'; ?>

    <main class="halaman-dashboard halaman-dash-rekruter">
        <section class="isi-dashboard">

            <div class="sapaan">
                <h1>Halo, <?= htmlspecialchars($_SESSION['nama_user']) ?></h1>
                <p>Ringkasan event dan pendaftar yang kamu kelola.</p>
            </div>

            <div class="ringkasan">
                <div class="ringkas-item">
                    <strong><?= $jumlahEventAktif ?></strong>
                    <span>Event aktif</span>
                </div>
                <div class="ringkas-item">
                    <strong><?= $totalPendaftar ?></strong>
                    <span>Total pendaftar</span>
                </div>
                <div class="ringkas-item">
                    <strong class="warna-menunggu"><?= $jumlahMenunggu ?></strong>
                    <span>Perlu diperiksa</span>
                </div>
                <div class="ringkas-item">
                    <strong class="warna-diterima"><?= $jumlahDiterima ?></strong>
                    <span>Diterima</span>
                </div>
            </div>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Perlu diperiksa</h2>
                    <a href="pendaftar.php">Lihat semua pendaftar</a>
                </div>

                <div class="panel-dash">
                    <?php if (empty($perluDiperiksaTerbaru)): ?>
                        <p style="padding: 16px; color: #64748b;">Tidak ada pendaftaran yang menunggu pemeriksaan saat ini.</p>
                    <?php else: ?>
                        <table class="tabel-mini">
                            <thead>
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Event</th>
                                    <th>Divisi</th>
                                    <th>Tanggal daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($perluDiperiksaTerbaru as $p): ?>
                                    <tr>
                                        <td>
                                            <span class="utama"><?= htmlspecialchars($p['nama_user']) ?></span>
                                            <span class="sub">NIM <?= htmlspecialchars($p['nim']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($p['nama_event']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_divisi']) ?></td>
                                        <td><?= htmlspecialchars($p['tanggal_pendaftaran'] ?? '-') ?></td>
                                        <td><a href="periksa_pendaftaran.php?id=<?= (int) $p['id_pendaftaran'] ?>" class="btn-mini">Periksa</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Event saya</h2>
                    <a href="kelola_event.php">Kelola semua event</a>
                </div>

                <div class="panel-dash">
                    <?php if (empty($eventSayaTerbaru)): ?>
                        <p style="padding: 16px; color: #64748b;">Kamu belum membuat event apa pun. <a href="kelola_event.php" style="text-decoration: underline;">Buat event sekarang</a>.</p>
                    <?php else: ?>
                        <?php foreach ($eventSayaTerbaru as $ev): ?>
                            <?php
                                $idEv        = (int) $ev->getIdEvent();
                                $namaEv      = $ev->getNamaEvent();
                                $inisial     = strtoupper(substr($namaEv, 0, 2));
                                $isOpen      = ($ev->getStatusEvent() === 'open_recruitment');
                                $jmlDivisi   = (int) ($jumlahDivisiPerEvent[$idEv] ?? 0);
                                $jmlDaftar   = (int) ($jumlahPendaftarPerEvent[$idEv] ?? 0);
                            ?>
                            <div class="baris-event">
                                <div class="logo-mini"><?= htmlspecialchars($inisial) ?></div>
                                <div class="baris-info">
                                    <strong><?= htmlspecialchars($namaEv) ?></strong>
                                    <small><?= htmlspecialchars($ev->getTanggalEvent()) ?> &middot; <?= htmlspecialchars($ev->getStatusEvent()) ?></small>
                                </div>
                                <span class="status-mini <?= $isOpen ? 'warna-diterima' : 'warna-tutup' ?>">
                                    <?= $isOpen ? 'Buka' : 'Tutup' ?>
                                </span>
                                <span class="batas"><?= $jmlDivisi ?> divisi &middot; <?= $jmlDaftar ?> pendaftar</span>
                                <a href="kelola_divisi.php?id=<?= $idEv ?>" class="btn-mini">Kelola</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>