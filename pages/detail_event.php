<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
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

$halaman = 'event';
$db = Database::getConnection();

// 1. Ambil data lengkap mahasiswa yang sedang login (nama, email, nim)
$stmtMhs = $db->prepare("
    SELECT m.id_mahasiswa, m.nim, u.nama_user, u.email 
    FROM mahasiswa m 
    JOIN user u ON u.id_user = m.id_user 
    WHERE m.id_user = ?
");
$stmtMhs->execute([$_SESSION['id_user']]);
$mhs = $stmtMhs->fetch(\PDO::FETCH_ASSOC);

// 2. Ambil daftar divisi pada event ini
$daftarDivisi = Divisi::byEvent($id_event);

// 3. Cek divisi mana saja yang sudah didaftari mahasiswa ini
$divisiSudahDaftar = [];
if ($mhs) {
    $stmtCek = $db->prepare("SELECT id_divisi FROM pendaftaran WHERE id_mahasiswa = ?");
    $stmtCek->execute([$mhs['id_mahasiswa']]);
    $divisiSudahDaftar = $stmtCek->fetchAll(\PDO::FETCH_COLUMN);
}

// 4. Hitung terisi kuota per divisi (yang ditolak tidak dihitung)
$stmtKuota = $db->prepare("
    SELECT id_divisi, COUNT(*) as terisi 
    FROM pendaftaran 
    WHERE status_pendaftaran <> 'ditolak' 
    GROUP BY id_divisi
");
$stmtKuota->execute();
$kuotaTerisi = $stmtKuota->fetchAll(\PDO::FETCH_KEY_PAIR);

$isOpen = ($event->getStatusEvent() === 'open_recruitment');

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event->getNamaEvent()) ?> - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/form-daftar.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="halaman-form-daftar">
        <section class="isi-dashboard">
            <div class="form-wadah">

                <a href="events.php" class="kembali">&larr; Kembali ke daftar event</a>

                <div class="info-event">
                    <h1><?= htmlspecialchars($event->getNamaEvent()) ?></h1>
                    <p>Pelaksanaan <?= htmlspecialchars($event->getTanggalEvent()) ?> &bull; Status: <?= htmlspecialchars($event->getStatusEvent()) ?></p>
                </div>

                <?php if ($sukses): ?>
                    <div style="background: #dcfce7; color: #15803d; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        <?= htmlspecialchars($sukses) ?>
                        <a href="pendaftaran.php" style="font-weight: 600; text-decoration: underline; margin-left: 6px;">Lihat pendaftaran saya &rarr;</a>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!$isOpen): ?>
                    <div style="background: #fef3c7; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        Pendaftaran untuk event ini belum dibuka atau sudah ditutup.
                    </div>
                <?php endif; ?>

                <form id="form-pendaftaran" action="../proses/proses_daftar.php" method="post">
                    <input type="hidden" name="id_event" value="<?= $id_event ?>">

                    <h2>Data mahasiswa</h2>
                    <div class="data-mhs">
                        <div class="kolom">
                            <label for="nama">Nama lengkap</label>
                            <input type="text" id="nama" value="<?= htmlspecialchars($mhs['nama_user'] ?? $_SESSION['nama_user']) ?>" readonly>
                        </div>
                        <div class="kolom">
                            <label for="nim">NIM</label>
                            <input type="text" id="nim" value="<?= htmlspecialchars($mhs['nim'] ?? '-') ?>" readonly>
                        </div>
                        <div class="kolom penuh">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?= htmlspecialchars($mhs['email'] ?? '-') ?>" readonly>
                        </div>
                    </div>

                    <h2>Pilih divisi</h2>
                    <p class="petunjuk">Pilih satu divisi. Kamu bisa mendaftar ke divisi lain setelahnya.</p>

                    <div class="daftar-divisi">
                        <?php if (empty($daftarDivisi)): ?>
                            <p style="color: #64748b;">Belum ada divisi untuk event ini.</p>
                        <?php else: ?>
                            <?php foreach ($daftarDivisi as $d): ?>
                                <?php
                                    $idDivisi     = (int) $d->getIdDivisi();
                                    $totalKuota   = (int) $d->getKuota();
                                    $terisi       = (int) ($kuotaTerisi[$idDivisi] ?? 0);
                                    $sisaKuota    = max(0, $totalKuota - $terisi);
                                    $sudahDaftar  = in_array($idDivisi, $divisiSudahDaftar);
                                    $penuh        = ($sisaKuota <= 0);
                                    $nonaktif     = (!$isOpen || $sudahDaftar || $penuh);

                                    if ($sudahDaftar) {
                                        $labelKuota = "Sudah mendaftar";
                                    } elseif ($penuh) {
                                        $labelKuota = "Kuota penuh";
                                    } else {
                                        $labelKuota = "Sisa kuota {$sisaKuota} dari {$totalKuota}";
                                    }
                                ?>
                                <label class="divisi <?= $nonaktif ? 'nonaktif' : '' ?>">
                                    <input type="radio" name="id_divisi" value="<?= $idDivisi ?>" <?= $nonaktif ? 'disabled' : 'required' ?>>
                                    <span class="divisi-isi">
                                        <span class="divisi-judul">
                                            <strong><?= htmlspecialchars($d->getNamaDivisi()) ?></strong>
                                            <span class="kuota"><?= htmlspecialchars($labelKuota) ?></span>
                                        </span>
                                        <small><b>Kuota Total:</b> <?= $totalKuota ?> anggota</small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($isOpen && !empty($daftarDivisi)): ?>
                        <button type="submit" class="btn-kirim">Kirim pendaftaran</button>
                    <?php endif; ?>
                </form>

            </div>
        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>