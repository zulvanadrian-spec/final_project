<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Event;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'panitia') {
    header("Location: login.php");
    exit;
}

$daftarEvent = Event::milikPanitia($_SESSION['id_user']);

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<a href="dashboard_panitia.php">&larr; Kembali</a>
<h2>Kelola Event</h2>

<?php if ($sukses) { ?><p style="color:green;"><?= htmlspecialchars($sukses) ?></p><?php } ?>
<?php if ($error) { ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php } ?>

<h3>Tambah Event</h3>
<form action="../proses/proses_event.php" method="POST">
    <input type="hidden" name="aksi" value="tambah">
    <input type="text" name="nama_event" placeholder="Nama event" required><br>
    <input type="date" name="tanggal_event" required><br>
    <select name="status_event">
        <option value="direncanakan">Direncanakan</option>
        <option value="open_recruitment">Open Recruitment</option>
        <option value="selesai">Selesai</option>
    </select><br>
    <button type="submit">Simpan</button>
</form>

<h3>Event Saya</h3>
<table border="1" cellpadding="6">
    <tr><th>Nama</th><th>Tanggal</th><th>Status</th></tr>
    <?php foreach ($daftarEvent as $e) { ?>
    <tr>
        <td><?= htmlspecialchars($e->getNamaEvent()) ?></td>
        <td><?= htmlspecialchars($e->getTanggalEvent()) ?></td>
        <td><?= htmlspecialchars($e->getStatusEvent()) ?></td>
    </tr>
    <?php } ?>
</table>

<?php if (empty($daftarEvent)) { ?><p>Belum ada event.</p><?php } ?>