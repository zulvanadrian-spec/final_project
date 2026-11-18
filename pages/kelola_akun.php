<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Admin;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$daftarAkun = Admin::semuaAkun();
$pengajuan = Admin::pengajuanMenunggu();

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<a href="dashboard_admin.php">&larr; Kembali</a>
<h2>Kelola Akun</h2>

<?php if ($sukses) { ?><p style="color:green;"><?= htmlspecialchars($sukses) ?></p><?php } ?>
<?php if ($error) { ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php } ?>

<h3>Buat Akun Panitia</h3>
<form action="../proses/proses_akun.php" method="POST">
    <input type="hidden" name="aksi" value="buat_panitia">
    <input type="text" name="nama_user" placeholder="Nama" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password awal (min. 6 karakter)" required><br>
    <button type="submit">Buat Akun</button>
</form>

<h3>Pengajuan Panitia</h3>
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
                <button type="submit" name="keputusan" value="disetujui">Setujui</button>
                <button type="submit" name="keputusan" value="ditolak">Tolak</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>
<?php } ?>

<h3>Daftar Akun</h3>
<table border="1" cellpadding="6">
    <tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
    <?php foreach ($daftarAkun as $a) { ?>
    <tr>
        <td><?= htmlspecialchars($a['nama_user']) ?></td>
        <td><?= htmlspecialchars($a['email']) ?></td>
        <td><?= htmlspecialchars($a['role']) ?></td>
        <td>
        <?php if ($a['role'] === 'mahasiswa') { ?>
            <form action="../proses/proses_akun.php" method="POST" style="display:inline"
                  onsubmit="return confirm('Jadikan akun ini panitia?')">
                <input type="hidden" name="aksi" value="jadikan_panitia">
                <input type="hidden" name="id_user" value="<?= (int) $a['id_user'] ?>">
                <button type="submit">Jadikan Panitia</button>
            </form>
        <?php } else { echo '-'; } ?>
        </td>
    </tr>
    <?php } ?>
</table>