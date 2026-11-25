<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Event;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'rekruter') {
    header("Location: login.php");
    exit;
}

$id_event = (int) ($_GET['id'] ?? 0);
$event = Event::cari($id_event);

if (!$event || !Event::milikRekruterIni($id_event, $_SESSION['id_user'])) {
    header("Location: kelola_event.php");
    exit;
}

$halaman = 'event';
$pilihanStatus = [
    'direncanakan'     => 'Direncanakan',
    'open_recruitment' => 'Open Recruitment',
    'selesai'          => 'Selesai',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Event - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/kelola-event.css">
    <link rel="stylesheet" href="../css/kelola-divisi.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar-rekruter.php'; ?>

    <main class="halaman-pendaftaran halaman-kelola halaman-divisi">
        <section class="isi-dashboard">

            <a href="kelola_event.php" class="kembali">&larr; Kembali ke kelola event</a>

            <form class="panel-form" action="../proses/proses_event.php" method="POST">
                <input type="hidden" name="aksi" value="ubah">
                <input type="hidden" name="id_event" value="<?= (int) $event->getIdEvent() ?>">

                <h2>Ubah event</h2>

                <div class="dua-kolom">
                    <div class="kolom">
                        <label for="nama_event">Nama event</label>
                        <input type="text" id="nama_event" name="nama_event" value="<?= htmlspecialchars($event->getNamaEvent()) ?>" required>
                    </div>
                    <div class="kolom">
                        <label for="tanggal_event">Tanggal event</label>
                        <input type="date" id="tanggal_event" name="tanggal_event" value="<?= htmlspecialchars($event->getTanggalEvent()) ?>" required>
                    </div>
                </div>

                <div class="kolom">
                    <label for="status_event">Status event</label>
                    <select id="status_event" name="status_event">
                        <?php foreach ($pilihanStatus as $nilai => $label): ?>
                            <option value="<?= $nilai ?>" <?= $event->getStatusEvent() === $nilai ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="aksi-form">
                    <button type="submit" class="btn-simpan">Simpan perubahan</button>
                </div>
            </form>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>