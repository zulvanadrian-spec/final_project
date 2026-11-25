<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\Event;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'rekruter') {
    header("Location: login.php");
    exit;
}

$halaman = 'event';
$daftarEvent = Event::milikRekruter($_SESSION['id_user']);

// Hitung jumlah divisi & jumlah pendaftar per event
$db = Database::getConnection();
$jumlahDivisiPerEvent = $db->query("SELECT id_event, COUNT(*) FROM divisi GROUP BY id_event")->fetchAll(\PDO::FETCH_KEY_PAIR);
$jumlahPendaftarPerEvent = $db->query("
    SELECT d.id_event, COUNT(p.id_pendaftaran) 
    FROM divisi d 
    LEFT JOIN pendaftaran p ON p.id_divisi = d.id_divisi 
    GROUP BY d.id_event
")->fetchAll(\PDO::FETCH_KEY_PAIR);

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Event - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/kelola-event.css">
    <style>
        .panel-form {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 24px;
            margin-top: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }
        .panel-form h2 {
            font-size: 18px;
            margin-bottom: 16px;
            color: #0f172a;
        }
        .panel-form .dua-kolom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .panel-form .kolom {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }
        .panel-form label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .panel-form input,
        .panel-form select {
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        .panel-form .btn-simpan {
            background: #4f6f52;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        @media (max-width: 640px) {
            .panel-form .dua-kolom { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar-rekruter.php'; ?>

    <main class="halaman-pendaftaran halaman-kelola">
        <section class="isi-dashboard">

            <div class="kepala-halaman">
                <div>
                    <h1>Kelola event</h1>
                    <p>Event yang kamu kelola beserta divisi dan jumlah pendaftarnya.</p>
                </div>
            </div>

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

            <div class="filter-kelola">
                <input type="search" id="cari" placeholder="Cari nama event">
                <select id="f-status">
                    <option value="">Semua status</option>
                    <option value="buka">Open Recruitment</option>
                    <option value="tutup">Direncanakan / Selesai</option>
                </select>
            </div>

            <div class="tabel-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Tanggal event</th>
                            <th>Status</th>
                            <th>Divisi</th>
                            <th>Pendaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isi-tabel">
                        <?php if (empty($daftarEvent)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 24px; color: #64748b;">
                                    Belum ada event. Tambahkan event baru lewat formulir di bawah.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftarEvent as $e): ?>
                                <?php
                                    $idEv       = (int) $e->getIdEvent();
                                    $statusRaw  = $e->getStatusEvent();
                                    $isOpen     = ($statusRaw === 'open_recruitment');
                                    $dataStatus = $isOpen ? 'buka' : 'tutup';
                                    $jmlDiv     = (int) ($jumlahDivisiPerEvent[$idEv] ?? 0);
                                    $jmlPend    = (int) ($jumlahPendaftarPerEvent[$idEv] ?? 0);
                                ?>
                                <tr data-status="<?= $dataStatus ?>" data-nama="<?= htmlspecialchars($e->getNamaEvent()) ?>" data-pendaftar="<?= $jmlPend ?>">
                                    <td><span class="utama"><?= htmlspecialchars($e->getNamaEvent()) ?></span></td>
                                    <td><?= htmlspecialchars($e->getTanggalEvent()) ?></td>
                                    <td>
                                        <span class="status <?= $isOpen ? 'buka' : 'tutup' ?>">
                                            <?= htmlspecialchars($statusRaw) ?>
                                        </span>
                                    </td>
                                    <td><?= $jmlDiv ?> divisi</td>
                                    <td><?= $jmlPend ?> orang</td>
                                    <td class="sel-aksi">
                                        <a href="kelola_divisi.php?id=<?= $idEv ?>" class="aksi aksi-detail">Divisi</a>
                                        <a href="edit_event.php?id=<?= $idEv ?>" class="aksi aksi-detail">Ubah</a>
                                        <form action="../proses/proses_event.php" method="POST" style="display:inline" onsubmit="return confirm('Hapus event ini?')">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_event" value="<?= $idEv ?>">
                                            <button type="submit" class="aksi aksi-hapus">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <form class="panel-form" action="../proses/proses_event.php" method="POST" style="margin-top: 24px;">
                <input type="hidden" name="aksi" value="tambah">
                <h2>Tambah event baru</h2>

                <div class="dua-kolom">
                    <div class="kolom">
                        <label for="nama_event">Nama event</label>
                        <input type="text" id="nama_event" name="nama_event" placeholder="Contoh: Seminar Nasional Teknologi" required>
                    </div>
                    <div class="kolom">
                        <label for="tanggal_event">Tanggal event</label>
                        <input type="date" id="tanggal_event" name="tanggal_event" required>
                    </div>
                </div>

                <div class="kolom">
                    <label for="status_event">Status awal</label>
                    <select id="status_event" name="status_event">
                        <option value="direncanakan">Direncanakan</option>
                        <option value="open_recruitment">Open Recruitment</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>

                <div class="aksi-form">
                    <button type="submit" class="btn-simpan">Simpan event</button>
                </div>
            </form>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>