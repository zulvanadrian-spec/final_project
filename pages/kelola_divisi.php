<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Event;
use pbo_FP\Models\Divisi;

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

$daftarDivisi = Divisi::byEvent($id_event);

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<a href="kelola_event.php">&larr; Kembali</a>
<h2>Divisi: <?= htmlspecialchars($event->getNamaEvent()) ?></h2>

<?php if ($sukses) { ?><p style="color:green;"><?= htmlspecialchars($sukses) ?></p><?php } ?>
<?php if ($error) { ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php } ?>

<h3>Tambah Divisi</h3>
<form action="../proses/proses_divisi.php" method="POST">
    <input type="hidden" name="aksi" value="tambah">
    <input type="hidden" name="id_event" value="<?= $id_event ?>">
    <input type="text" name="nama_divisi" placeholder="Nama divisi" required>
    <input type="number" name="kuota" min="1" placeholder="Kuota" required>
    <button type="submit">Tambah</button>
</form>

<h3>Daftar Divisi</h3>
<?php foreach ($daftarDivisi as $d) { ?>
    <form action="../proses/proses_divisi.php" method="POST">
        <input type="hidden" name="aksi" value="ubah">
        <input type="hidden" name="id_event" value="<?= $id_event ?>">
        <input type="hidden" name="id_divisi" value="<?= (int) $d->getIdDivisi() ?>">
        <input type="text" name="nama_divisi" value="<?= htmlspecialchars($d->getNamaDivisi()) ?>" required>
        <input type="number" name="kuota" min="1" value="<?= (int) $d->getKuota() ?>" required>
        <button type="submit">Simpan</button>
    </form>
<?php } ?>

<?php if (empty($daftarDivisi)) { ?><p>Belum ada divisi.</p><?php } ?>