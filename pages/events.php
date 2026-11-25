<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\Event;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$halaman = 'event';
$daftarEvent = Event::semua();

// Ambil daftar divisi per event agar kapsul seksi tampil dinamis sesuai database
$db = Database::getConnection();
$stmtDiv = $db->query("SELECT id_event, nama_divisi FROM divisi");
$divisiPerEvent = [];
while ($row = $stmtDiv->fetch(\PDO::FETCH_ASSOC)) {
    $divisiPerEvent[$row['id_event']][] = $row['nama_divisi'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/event.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="halaman-event">
        <section class="isi-dashboard">

            <div class="filter-baris">
                <input type="search" id="cari" placeholder="Cari event atau penyelenggara">
                <select id="status">
                    <option value="semua">Semua status</option>
                    <option value="buka">Pendaftaran buka</option>
                    <option value="tutup">Pendaftaran tutup</option>
                </select>
            </div>

            <div class="daftar-event">
                <?php if (empty($daftarEvent)): ?>
                    <p style="color: #64748b;">Belum ada event yang tersedia saat ini.</p>
                <?php else: ?>
                    <?php foreach ($daftarEvent as $e): ?>
                        <?php
                            $idEvent     = (int) $e->getIdEvent();
                            $namaEvent   = $e->getNamaEvent();
                            $tanggal     = $e->getTanggalEvent();
                            $statusRaw   = strtolower($e->getStatusEvent());
                            
                            // Cek apakah status event ditutup/selesai atau masih buka
                            // Cek apakah status event terbuka (open_recruitment) atau tidak
                            $isTutup     = ($statusRaw !== 'open_recruitment');
                            $dataStatus  = $isTutup ? 'tutup' : 'buka';
                            $inisial     = strtoupper(substr($namaEvent, 0, 2));
                            $listDivisi  = $divisiPerEvent[$idEvent] ?? [];
                        ?>
                        <article class="event <?= $isTutup ? 'tutup' : '' ?>" data-status="<?= $dataStatus ?>">
                            <div class="event-atas">
                                <div class="logo-pen"><?= htmlspecialchars($inisial) ?></div>
                            </div>

                            <p class="penyelenggara">Status: <?= htmlspecialchars(ucfirst($statusRaw)) ?></p>
                            <h3><?= htmlspecialchars($namaEvent) ?></h3>

                            <p class="label-seksi">Seksi dibutuhkan</p>
                            <div class="kapsul-wrap">
                                <?php if (empty($listDivisi)): ?>
                                    <span class="kapsul">Belum ada divisi</span>
                                <?php else: ?>
                                    <?php foreach (array_slice($listDivisi, 0, 3) as $namaDiv): ?>
                                        <span class="kapsul"><?= htmlspecialchars($namaDiv) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($listDivisi) > 3): ?>
                                        <span class="kapsul">+<?= count($listDivisi) - 3 ?> lainnya</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="event-bawah">
                                <div class="info-tanggal">
                                    <strong><?= htmlspecialchars($tanggal) ?></strong>
                                    <small>Kepanitiaan Kampus</small>
                                </div>
                                <?php if ($isTutup): ?>
                                    <span class="btn-daftar-event nonaktif">Pendaftaran ditutup</span>
                                <?php else: ?>
                                    <a href="detail_event.php?id=<?= $idEvent ?>" class="btn-daftar-event">Daftar sekarang</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <p class="kosong" id="kosong" hidden>Tidak ada event yang cocok. Coba kata kunci lain.</p>

        </section>
    </main>

    <script src="../js/event.js"></script>
</body>
</html>