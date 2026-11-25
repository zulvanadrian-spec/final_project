<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
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

$halaman = 'event';
$daftarDivisi = Divisi::byEvent($id_event);

// Hitung jumlah pendaftar terisi per divisi (yang tidak ditolak)
$db = Database::getConnection();
$stmtTerisi = $db->prepare("
    SELECT id_divisi, COUNT(*) 
    FROM pendaftaran 
    WHERE status_pendaftaran <> 'ditolak' 
    GROUP BY id_divisi
");
$stmtTerisi->execute();
$terisiPerDivisi = $stmtTerisi->fetchAll(\PDO::FETCH_KEY_PAIR);

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Divisi - Rekruterin</title>
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

            <div class="kepala-halaman">
                <div>
                    <h1>Divisi</h1>
                    <p><?= htmlspecialchars($event->getNamaEvent()) ?></p>
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

            <div class="tabel-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Divisi</th>
                            <th>Kuota</th>
                            <th>Terisi</th>
                            <th>Simpan Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftarDivisi)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 24px; color: #64748b;">
                                    Belum ada divisi untuk event ini. Tambahkan lewat formulir di bawah.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftarDivisi as $d): ?>
                                <?php
                                    $idDiv   = (int) $d->getIdDivisi();
                                    $kuota   = (int) $d->getKuota();
                                    $terisi  = (int) ($terisiPerDivisi[$idDiv] ?? 0);
                                ?>
                                <tr>
                                    <form action="../proses/proses_divisi.php" method="POST">
                                        <input type="hidden" name="aksi" value="ubah">
                                        <input type="hidden" name="id_event" value="<?= $id_event ?>">
                                        <input type="hidden" name="id_divisi" value="<?= $idDiv ?>">
                                        <td>
                                            <input type="text" name="nama_divisi" value="<?= htmlspecialchars($d->getNamaDivisi()) ?>" required style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; width: 100%;">
                                        </td>
                                        <td>
                                            <input type="number" name="kuota" min="1" value="<?= $kuota ?>" required style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; width: 90px;">
                                        </td>
                                        <td><?= $terisi ?> dari <?= $kuota ?></td>
                                        <td class="sel-aksi">
                                            <button type="submit" class="aksi aksi-detail">Simpan</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <form class="panel-form" action="../proses/proses_divisi.php" method="POST" style="margin-top: 24px;">
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="id_event" value="<?= $id_event ?>">
                <h2>Tambah divisi</h2>

                <div class="dua-kolom">
                    <div class="kolom">
                        <label for="nama_divisi">Nama divisi</label>
                        <input type="text" id="nama_divisi" name="nama_divisi" placeholder="Contoh: Acara / Humas" required>
                    </div>
                    <div class="kolom">
                        <label for="kuota">Kuota (orang)</label>
                        <input type="number" id="kuota" name="kuota" min="1" placeholder="Contoh: 10" required>
                    </div>
                </div>

                <div class="aksi-form">
                    <button type="submit" class="btn-simpan">Tambah divisi</button>
                </div>
            </form>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>