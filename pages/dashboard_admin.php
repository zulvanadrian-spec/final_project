<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\Admin;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$halaman = 'dashboard';
$db = Database::getConnection();

$semuaAkun = Admin::semuaAkun();
$pengajuanMenunggu = Admin::pengajuanMenunggu();

$totalAkun      = count($semuaAkun);
$totalMahasiswa = 0;
$totalRekruter  = 0;
foreach ($semuaAkun as $ak) {
    if ($ak['role'] === 'mahasiswa') $totalMahasiswa++;
    elseif ($ak['role'] === 'rekruter') $totalRekruter++;
}
$jmlMenungguVerif = count($pengajuanMenunggu);

// Statistik event & pendaftaran di seluruh sistem
$totalEvent         = (int) $db->query("SELECT COUNT(*) FROM event")->fetchColumn();
$eventBuka          = (int) $db->query("SELECT COUNT(*) FROM event WHERE status_event = 'open_recruitment'")->fetchColumn();
$totalPendaftaran   = (int) $db->query("SELECT COUNT(*) FROM pendaftaran")->fetchColumn();
$belumDiperiksa     = (int) $db->query("SELECT COUNT(*) FROM pendaftaran WHERE status_pendaftaran = 'menunggu'")->fetchColumn();

// 5 akun terbaru
$akunTerbaru = array_slice(array_reverse($semuaAkun), 0, 5);

// Daftar seluruh event beserta nama rekruter, jumlah divisi, dan jumlah pendaftar
$daftarEventSistem = $db->query("
    SELECT 
        e.id_event, e.nama_event, e.tanggal_event, e.status_event,
        u.nama_user AS nama_rekruter,
        (SELECT COUNT(*) FROM divisi d WHERE d.id_event = e.id_event) AS jml_divisi,
        (SELECT COUNT(*) FROM pendaftaran p JOIN divisi d2 ON d2.id_divisi = p.id_divisi WHERE d2.id_event = e.id_event) AS jml_pendaftar
    FROM event e
    JOIN rekruter r ON r.id_rekruter = e.id_rekruter
    JOIN user u ON u.id_user = r.id_user
    ORDER BY e.id_event DESC
")->fetchAll(\PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/dashboard-admin.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar-admin.php'; ?>

    <main class="halaman-dashboard halaman-dash-admin">
        <section class="isi-dashboard">

            <div class="sapaan">
                <h1>Halo, <?= htmlspecialchars($_SESSION['nama_user'] ?? 'Admin') ?></h1>
                <p>Ringkasan akun dan data di seluruh sistem.</p>
            </div>

            <?php if ($jmlMenungguVerif > 0): ?>
            <div class="perlu-tindakan">
                <div>
                    <strong><?= $jmlMenungguVerif ?> permohonan rekruter menunggu verifikasi</strong>
                    <small>Periksa pengajuannya, lalu setujui atau tolak agar mereka bisa mulai mengelola event.</small>
                </div>
                <a href="verifikasi.php" class="btn-mini">Periksa sekarang</a>
            </div>
            <?php endif; ?>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Akun</h2>
                    <a href="kelola_akun.php">Kelola akun</a>
                </div>
                <div class="ringkasan">
                    <div class="ringkas-item">
                        <strong><?= $totalAkun ?></strong>
                        <span>Total akun</span>
                    </div>
                    <div class="ringkas-item">
                        <strong><?= $totalMahasiswa ?></strong>
                        <span>Mahasiswa</span>
                    </div>
                    <div class="ringkas-item">
                        <strong><?= $totalRekruter ?></strong>
                        <span>Rekruter</span>
                    </div>
                    <div class="ringkas-item">
                        <strong class="warna-menunggu"><?= $jmlMenungguVerif ?></strong>
                        <span>Menunggu verifikasi</span>
                    </div>
                </div>
            </div>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Data sistem</h2>
                </div>
                <div class="ringkasan">
                    <div class="ringkas-item">
                        <strong><?= $totalEvent ?></strong>
                        <span>Total event</span>
                    </div>
                    <div class="ringkas-item">
                        <strong class="warna-diterima"><?= $eventBuka ?></strong>
                        <span>Event buka</span>
                    </div>
                    <div class="ringkas-item">
                        <strong><?= $totalPendaftaran ?></strong>
                        <span>Total pendaftaran</span>
                    </div>
                    <div class="ringkas-item">
                        <strong class="warna-menunggu"><?= $belumDiperiksa ?></strong>
                        <span>Belum diperiksa rekruter</span>
                    </div>
                </div>
            </div>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Akun terbaru</h2>
                    <a href="kelola_akun.php">Lihat semua akun</a>
                </div>

                <div class="panel-dash">
                    <table class="tabel-mini">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Peran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($akunTerbaru)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 16px; color: #64748b;">Belum ada akun terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($akunTerbaru as $ak): ?>
                                    <?php
                                        $role = $ak['role'];
                                        $badgeClass = $role === 'admin' ? 'peran-admin' : ($role === 'rekruter' ? 'peran-panitia' : 'peran-mahasiswa');
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="utama"><?= htmlspecialchars($ak['nama_user']) ?></span>
                                            <span class="sub"><?= htmlspecialchars($ak['email']) ?></span>
                                        </td>
                                        <td><span class="peran-badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($role)) ?></span></td>
                                        <td><span class="status-mini warna-diterima">Aktif</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bagian-dash">
                <div class="bagian-kepala">
                    <h2>Event di seluruh sistem</h2>
                    <span class="hanya-baca">Hanya dibaca</span>
                </div>

                <div class="panel-dash">
                    <table class="tabel-mini tabel-event">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Rekruter</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Divisi</th>
                                <th>Pendaftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftarEventSistem)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 16px; color: #64748b;">Belum ada event di sistem.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daftarEventSistem as $ev): ?>
                                    <?php $isOpen = ($ev['status_event'] === 'open_recruitment'); ?>
                                    <tr>
                                        <td><span class="utama"><?= htmlspecialchars($ev['nama_event']) ?></span></td>
                                        <td><?= htmlspecialchars($ev['nama_rekruter']) ?></td>
                                        <td><?= htmlspecialchars($ev['tanggal_event']) ?></td>
                                        <td>
                                            <span class="status-mini <?= $isOpen ? 'warna-diterima' : 'warna-nonaktif' ?>">
                                                <?= htmlspecialchars($ev['status_event']) ?>
                                            </span>
                                        </td>
                                        <td><?= (int) $ev['jml_divisi'] ?></td>
                                        <td><?= (int) $ev['jml_pendaftar'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>