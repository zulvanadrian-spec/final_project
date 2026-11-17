<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Event;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'panitia') {
    header("Location: login.php");
    exit;
}

$id_event = (int) ($_GET['id'] ?? 0);
$event = Event::cari($id_event);

if (!$event || !Event::milikPanitiaIni($id_event, $_SESSION['id_user'])) {
    header("Location: kelola_event.php");
    exit;
}

$pilihanStatus = [
    'direncanakan'     => 'Direncanakan',
    'open_recruitment' => 'Open Recruitment',
    'selesai'          => 'Selesai',
];
?>
<a href="kelola_event.php">&larr; Kembali</a>
<h2>Ubah Event</h2>

<form action="../proses/proses_event.php" method="POST">
    <input type="hidden" name="aksi" value="ubah">
    <input type="hidden" name="id_event" value="<?= (int) $event->getIdEvent() ?>">
    <input type="text" name="nama_event" value="<?= htmlspecialchars($event->getNamaEvent()) ?>" required><br>
    <input type="date" name="tanggal_event" value="<?= htmlspecialchars($event->getTanggalEvent()) ?>" required><br>
    <select name="status_event">
        <?php foreach ($pilihanStatus as $nilai => $label) { ?>
            <option value="<?= $nilai ?>" <?= $event->getStatusEvent() === $nilai ? 'selected' : '' ?>><?= $label ?></option>
        <?php } ?>
    </select><br>
    <button type="submit">Simpan Perubahan</button>
</form>