<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Event;
use pbo_FP\Models\Divisi;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$id_event = (int) ($_GET['id'] ?? 0);
$event = Event::cari($id_event);

if (!$event) {
    header("Location: events.php");
    exit;
}

$daftarDivisi = Divisi::byEvent($id_event);

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<a href="events.php">&larr; Kembali</a>

<h2><?= htmlspecialchars($event->getNamaEvent()) ?></h2>
<p><?= htmlspecialchars($event->getTanggalEvent()) ?> | <?= htmlspecialchars($event->getStatusEvent()) ?></p>

<?php if ($event->getStatusEvent() !== 'open_recruitment') { ?>
    <p><em>Pendaftaran belum dibuka atau sudah ditutup.</em></p>
<?php } ?>

<?php if ($sukses) { ?><p style="color:green;"><?= htmlspecialchars($sukses) ?></p><?php } ?>
<?php if ($error) { ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php } ?>

<h3>Divisi</h3>

<?php foreach ($daftarDivisi as $d) { ?>
    <div>
        <strong><?= htmlspecialchars($d->getNamaDivisi()) ?></strong> (kuota: <?= (int) $d->getKuota() ?>)

    <?php if ($event->getStatusEvent() === 'open_recruitment') { ?>
    <form action="../proses/proses_daftar.php" method="POST">
        <input type="hidden" name="id_divisi" value="<?= (int) $d->getIdDivisi() ?>">
        <input type="hidden" name="id_event" value="<?= (int) $d->getIdEvent() ?>">
        <button type="submit">Daftar</button>
    </form>
    <?php } ?>
    </div>
<?php } ?>

<?php if (empty($daftarDivisi)) { ?>
    <p>Belum ada divisi untuk event ini.</p>
<?php } ?>