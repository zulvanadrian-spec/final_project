<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'rekruter') {
    header("Location: login.php");
    exit;
}

$id_pendaftaran = (int) ($_GET['id'] ?? 0);
$db = Database::getConnection();

// Ambil detail pendaftaran & pastikan milik event rekruter yang login
$stmt = $db->prepare("
    SELECT 
        p.id_pendaftaran, p.tanggal_pendaftaran, p.status_pendaftaran,
        u.nama_user, u.email, m.nim,
        d.id_divisi, d.nama_divisi, d.kuota,
        e.nama_event, e.tanggal_event
    FROM pendaftaran p
    JOIN mahasiswa m ON m.id_mahasiswa = p.id_mahasiswa
    JOIN user u ON u.id_user = m.id_user
    JOIN divisi d ON d.id_divisi = p.id_divisi
    JOIN event e ON e.id_event = d.id_event
    JOIN rekruter r ON r.id_rekruter = e.id_rekruter
    WHERE p.id_pendaftaran = ? AND r.id_user = ?
");
$stmt->execute([$id_pendaftaran, $_SESSION['id_user']]);
$data = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$data) {
    header("Location: pendaftar.php");
    exit;
}

// Hitung jumlah pendaftar yang diterima di divisi ini
$stmtTerisi = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE id_divisi = ? AND status_pendaftaran = 'diterima'");
$stmtTerisi->execute([$data['id_divisi']]);
$terisi = (int) $stmtTerisi->fetchColumn();
$kuota  = (int) $data['kuota'];
$sisa   = max(0, $kuota - $terisi);

// Cek riwayat review jika sudah diputuskan
$sudah_diputuskan = ($data['status_pendaftaran'] !== 'menunggu');
$hasilReview = null;
if ($sudah_diputuskan) {
    $stmtRev = $db->prepare("
        SELECT rv.keputusan, rv.catatan, rv.waktu_review, u.nama_user AS nama_rekruter
        FROM review rv
        JOIN rekruter r ON r.id_rekruter = rv.id_rekruter
        JOIN user u ON u.id_user = r.id_user
        WHERE rv.id_pendaftaran = ?
        ORDER BY rv.id_review DESC LIMIT 1
    ");
    $stmtRev->execute([$id_pendaftaran]);
    $hasilReview = $stmtRev->fetch(\PDO::FETCH_ASSOC);
}

$halaman = 'pendaftar';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periksa Pendaftaran - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/periksa-pendaftaran.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar-rekruter.php'; ?>

    <main class="halaman-periksa">
        <section class="isi-dashboard">
            <div class="periksa-wadah">

                <a href="pendaftar.php" class="kembali">&larr; Kembali ke daftar pendaftar</a>
                <h1>Periksa pendaftaran</h1>

                <div class="panel">
                    <h2>Data pendaftaran</h2>

                    <dl class="data">
                        <div>
                            <dt>Nama mahasiswa</dt>
                            <dd><?= htmlspecialchars($data['nama_user']) ?></dd>
                        </div>
                        <div>
                            <dt>NIM</dt>
                            <dd><?= htmlspecialchars($data['nim']) ?></dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd><?= htmlspecialchars($data['email']) ?></dd>
                        </div>
                        <div>
                            <dt>Tanggal daftar</dt>
                            <dd><?= htmlspecialchars($data['tanggal_pendaftaran'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt>Event</dt>
                            <dd><?= htmlspecialchars($data['nama_event']) ?></dd>
                        </div>
                        <div>
                            <dt>Tanggal pelaksanaan</dt>
                            <dd><?= htmlspecialchars($data['tanggal_event']) ?></dd>
                        </div>
                        <div>
                            <dt>Divisi dipilih</dt>
                            <dd><?= htmlspecialchars($data['nama_divisi']) ?></dd>
                        </div>
                        <div>
                            <dt>Kuota divisi</dt>
                            <dd><?= $kuota ?> orang, diterima <?= $terisi ?>, sisa <?= $sisa ?></dd>
                        </div>
                        <div>
                            <dt>Status saat ini</dt>
                            <dd>
                                <span class="status <?= htmlspecialchars($data['status_pendaftaran']) ?>">
                                    <?= ucfirst(htmlspecialchars($data['status_pendaftaran'])) ?>
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <?php if (!$sudah_diputuskan): ?>

                <form class="panel" action="../proses/proses_review.php" method="POST">
                    <input type="hidden" name="id_pendaftaran" value="<?= $id_pendaftaran ?>">

                    <h2>Keputusan</h2>

                    <div class="pilihan-keputusan">
                        <label class="keputusan terima">
                            <input type="radio" name="keputusan" value="diterima" required>
                            <span>Diterima</span>
                        </label>
                        <label class="keputusan tolak">
                            <input type="radio" name="keputusan" value="ditolak" required>
                            <span>Ditolak</span>
                        </label>
                    </div>

                    <div class="kolom">
                        <label for="catatan">Catatan untuk mahasiswa</label>
                        <textarea id="catatan" name="catatan" rows="4"
                            placeholder="Contoh: Selamat bergabung, info rapat pertama dikirim lewat email."></textarea>
                        <small>Disarankan diisi, terutama jika pendaftaran ditolak agar mahasiswa mengetahui alasannya.</small>
                    </div>

                    <button type="submit" class="btn-simpan">Simpan keputusan</button>
                </form>

                <?php else: ?>

                <div class="panel">
                    <h2>Hasil pemeriksaan</h2>

                    <dl class="data">
                        <div>
                            <dt>Keputusan</dt>
                            <dd>
                                <span class="status <?= htmlspecialchars($data['status_pendaftaran']) ?>">
                                    <?= ucfirst(htmlspecialchars($data['status_pendaftaran'])) ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt>Diperiksa oleh</dt>
                            <dd><?= htmlspecialchars($hasilReview['nama_rekruter'] ?? $_SESSION['nama_user']) ?></dd>
                        </div>
                        <div>
                            <dt>Waktu pemeriksaan</dt>
                            <dd><?= htmlspecialchars($hasilReview['waktu_review'] ?? '-') ?></dd>
                        </div>
                        <div class="penuh">
                            <dt>Catatan</dt>
                            <dd><?= htmlspecialchars(($hasilReview['catatan'] ?? '') !== '' ? $hasilReview['catatan'] : '-') ?></dd>
                        </div>
                    </dl>
                </div>

                <?php endif; ?>

            </div>
        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>