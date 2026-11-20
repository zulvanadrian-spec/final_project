<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Admin;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pengajuan = Admin::pengajuanMenunggu();

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<a href="dashboard_admin.php">&larr; Kembali</a>
<h2>Kelola Akun</h2>

<?php if ($sukses) { ?><p style="color:green;"><?= htmlspecialchars($sukses) ?></p><?php } ?>
<?php if ($error) { ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php }?>

<h3>Pengajuan Rekruter</h3>
<?php if (empty($pengajuan)) { ?>
    <p>Tidak ada pengajuan menunggu.</p>
<?php } else { ?>

<table border="1" cellpadding="6">
    <tr><th>Nama</th><th>Email</th><th>Tanggal</th><th>Aksi</th></tr>
    <?php foreach ($pengajuan as $p) { ?>
    <tr>
        <td><?= htmlspecialchars($p['nama_user']) ?></td>
        <td><?= htmlspecialchars($p['email']) ?></td>
        <td><?= htmlspecialchars($p['tanggal_pengajuan']) ?></td>
        <td>
            <form action="../proses/proses_akun.php" method="POST" style="display:inline">
                <input type="hidden" name="aksi" value="putuskan_pengajuan">
                <input type="hidden" name="id_pengajuan" value="<?= (int) $p['id_pengajuan'] ?>">
                <button type="submit" name="keputusan" value="disetujui">Izinkan</button>
                <button type="submit" name="keputusan" value="ditolak">Tolak</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>
<?php } ?>