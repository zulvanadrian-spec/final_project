<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Pendaftaran;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'rekruter') {
    header("Location: login.php");
    exit;
}

$daftar = Pendaftaran::untukRekruter($_SESSION['id_user']);

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<a href="dashboard_rekruter.php">&larr; Kembali</a>
<h2>Daftar Pendaftar</h2>

<?php if ($sukses) { ?><p style="color:green;"><?= htmlspecialchars($sukses) ?></p><?php } ?>
<?php if ($error) { ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php } ?>

<table border="1" cellpadding="6">
    <tr><th>Nama</th><th>NIM</th><th>Event</th><th>Divisi</th><th>Status</th><th>Aksi</th></tr>
    <?php foreach ($daftar as $p) { ?>
    <tr>
        <td><?= htmlspecialchars($p['nama_user']) ?></td>
        <td><?= htmlspecialchars($p['nim']) ?></td>
        <td><?= htmlspecialchars($p['nama_event']) ?></td>
        <td><?= htmlspecialchars($p['nama_divisi']) ?></td>
        <td><?= htmlspecialchars($p['status_pendaftaran']) ?></td>
        <td>
        <?php if ($p['status_pendaftaran'] === 'menunggu') { ?>
            <form action="../proses/proses_review.php" method="POST">
                <input type="hidden" name="id_pendaftaran" value="<?= (int) $p['id_pendaftaran'] ?>">
                <input type="text" name="catatan" placeholder="Catatan (opsional)">
                <button type="submit" name="keputusan" value="diterima">Terima</button>
                <button type="submit" name="keputusan" value="ditolak">Tolak</button>
            </form>
        <?php } else { echo '-'; } ?>
        </td>
    </tr>
    <?php } ?>
</table>

<?php if (empty($daftar)) { ?><p>Belum ada pendaftar.</p><?php } ?>