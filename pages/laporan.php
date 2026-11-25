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

$halaman = 'laporan';
$id_user = $_SESSION['id_user'];
$eventSaya = Event::milikRekruter($id_user);

// Tentukan event yang dipilih dari ?id_event=... (default: event pertama milik rekruter)
$selectedEvent = null;
$idEventDipilih = (int) ($_GET['id_event'] ?? 0);

foreach ($eventSaya as $ev) {
    if ((int) $ev->getIdEvent() === $idEventDipilih) {
        $selectedEvent = $ev;
        break;
    }
}
if (!$selectedEvent && !empty($eventSaya)) {
    $selectedEvent = $eventSaya[0];
    $idEventDipilih = (int) $selectedEvent->getIdEvent();
}

$db = Database::getConnection();
$daftarDivisi = [];
$pendaftarEvent = [];

$totalPendaftar = 0;
$totalMenunggu  = 0;
$totalDiterima  = 0;
$totalDitolak   = 0;
$totalKuota     = 0;

$rekapDivisi = [];

if ($selectedEvent) {
    $daftarDivisi = Divisi::byEvent($idEventDipilih);

    // Ambil semua pendaftar + catatan review terakhir untuk event terpilih
    $stmt = $db->prepare("
        SELECT 
            p.id_pendaftaran, p.id_divisi, p.tanggal_pendaftaran, p.status_pendaftaran,
            u.nama_user, m.nim, d.nama_divisi,
            (SELECT rv.catatan FROM review rv WHERE rv.id_pendaftaran = p.id_pendaftaran ORDER BY rv.id_review DESC LIMIT 1) AS catatan
        FROM pendaftaran p
        JOIN mahasiswa m ON m.id_mahasiswa = p.id_mahasiswa
        JOIN user u ON u.id_user = m.id_user
        JOIN divisi d ON d.id_divisi = p.id_divisi
        WHERE d.id_event = ?
        ORDER BY p.tanggal_pendaftaran DESC, p.id_pendaftaran DESC
    ");
    $stmt->execute([$idEventDipilih]);
    $pendaftarEvent = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Siapkan bucket per divisi
    foreach ($daftarDivisi as $d) {
        $idDiv = (int) $d->getIdDivisi();
        $kuota = (int) $d->getKuota();
        $totalKuota += $kuota;

        $rekapDivisi[$idDiv] = [
            'nama_divisi' => $d->getNamaDivisi(),
            'kuota'       => $kuota,
            'total'       => 0,
            'diterima'    => 0,
            'ditolak'     => 0,
            'menunggu'    => 0,
        ];
    }

    // Hitung statistik keseluruhan & per divisi
    foreach ($pendaftarEvent as $p) {
        $totalPendaftar++;
        $st    = $p['status_pendaftaran'];
        $idDiv = (int) $p['id_divisi'];

        if ($st === 'diterima') {
            $totalDiterima++;
        } elseif ($st === 'ditolak') {
            $totalDitolak++;
        } else {
            $totalMenunggu++;
        }

        if (isset($rekapDivisi[$idDiv])) {
            $rekapDivisi[$idDiv]['total']++;
            if (isset($rekapDivisi[$idDiv][$st])) {
                $rekapDivisi[$idDiv][$st]++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/laporan.css">
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar-rekruter.php'; ?>

    <main class="halaman-dashboard halaman-laporan">
        <section class="isi-dashboard">

            <div class="kepala-laporan">
                <div>
                    <h1>Laporan pendaftaran</h1>
                    <p>Rekap pendaftar per event dan divisi.</p>
                </div>
                <?php if ($selectedEvent): ?>
                <div class="aksi-laporan">
                    <button type="button" class="btn-unduh" id="unduh-csv">Unduh Excel (CSV)</button>
                    <button type="button" class="btn-cetak" onclick="window.print()">Cetak / PDF</button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (empty($eventSaya)): ?>
                <div class="panel-dash" style="padding: 24px; color: #64748b;">
                    Kamu belum memiliki event. Buat event terlebih dahulu di halaman <a href="kelola_event.php" style="text-decoration: underline;">Kelola Event</a>.
                </div>
            <?php else: ?>

                <form class="filter-laporan" method="GET" action="laporan.php">
                    <label for="f-event">Event</label>
                    <select id="f-event" name="id_event" onchange="this.form.submit()">
                        <?php foreach ($eventSaya as $ev): ?>
                            <option value="<?= (int) $ev->getIdEvent() ?>" <?= (int) $ev->getIdEvent() === $idEventDipilih ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev->getNamaEvent()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php $isOpen = ($selectedEvent->getStatusEvent() === 'open_recruitment'); ?>
                <p class="info-event">
                    Pelaksanaan <?= htmlspecialchars($selectedEvent->getTanggalEvent()) ?> &middot; Status
                    <span class="status-mini <?= $isOpen ? 'warna-diterima' : 'warna-tutup' ?>">
                        <?= htmlspecialchars($selectedEvent->getStatusEvent()) ?>
                    </span>
                </p>

                <div class="ringkasan">
                    <div class="ringkas-item">
                        <strong><?= $totalPendaftar ?></strong>
                        <span>Total pendaftar</span>
                    </div>
                    <div class="ringkas-item">
                        <strong class="warna-menunggu"><?= $totalMenunggu ?></strong>
                        <span>Menunggu</span>
                    </div>
                    <div class="ringkas-item">
                        <strong class="warna-diterima"><?= $totalDiterima ?></strong>
                        <span>Diterima</span>
                    </div>
                    <div class="ringkas-item">
                        <strong class="warna-ditolak"><?= $totalDitolak ?></strong>
                        <span>Ditolak</span>
                    </div>
                </div>

                <div class="bagian-dash">
                    <div class="bagian-kepala">
                        <h2>Rekap per divisi</h2>
                    </div>

                    <div class="panel-dash">
                        <table class="tabel-mini">
                            <thead>
                                <tr>
                                    <th>Divisi</th>
                                    <th>Pendaftar</th>
                                    <th class="angka">Diterima</th>
                                    <th class="angka">Ditolak</th>
                                    <th class="angka">Menunggu</th>
                                    <th class="angka">Sisa kuota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rekapDivisi)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 16px; color: #64748b;">Belum ada divisi pada event ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rekapDivisi as $rd): ?>
                                        <?php
                                            $sisaKuota = max(0, $rd['kuota'] - $rd['diterima']);
                                            $persen    = $rd['kuota'] > 0 ? min(100, round(($rd['diterima'] / $rd['kuota']) * 100)) : 0;
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rd['nama_divisi']) ?></td>
                                            <td>
                                                <span class="teks-bar"><?= $rd['diterima'] ?> diterima dari <?= $rd['kuota'] ?> kuota (<?= $rd['total'] ?> pendaftar)</span>
                                                <div class="bar"><span style="width: <?= $persen ?>%"></span></div>
                                            </td>
                                            <td class="angka"><?= $rd['diterima'] ?></td>
                                            <td class="angka"><?= $rd['ditolak'] ?></td>
                                            <td class="angka"><?= $rd['menunggu'] ?></td>
                                            <td class="angka"><?= $sisaKuota ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($rekapDivisi)): ?>
                            <tfoot>
                                <?php
                                    $totalSisa   = max(0, $totalKuota - $totalDiterima);
                                    $totalPersen = $totalKuota > 0 ? min(100, round(($totalDiterima / $totalKuota) * 100)) : 0;
                                ?>
                                <tr>
                                    <td>Total</td>
                                    <td>
                                        <span class="teks-bar"><?= $totalDiterima ?> dari <?= $totalKuota ?></span>
                                        <div class="bar"><span style="width: <?= $totalPersen ?>%"></span></div>
                                    </td>
                                    <td class="angka"><?= $totalDiterima ?></td>
                                    <td class="angka"><?= $totalDitolak ?></td>
                                    <td class="angka"><?= $totalMenunggu ?></td>
                                    <td class="angka"><?= $totalSisa ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="bagian-dash">
                    <div class="bagian-kepala">
                        <h2>Daftar pendaftar</h2>
                    </div>

                    <div class="panel-dash">
                        <table class="tabel-mini" id="tabel-pendaftar-laporan">
                            <thead>
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Divisi</th>
                                    <th>Tanggal daftar</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendaftarEvent)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 16px; color: #64748b;">Belum ada pendaftar untuk event ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendaftarEvent as $p): ?>
                                        <?php
                                            $st = $p['status_pendaftaran'];
                                            $kelasWarna = $st === 'diterima' ? 'warna-diterima' : ($st === 'ditolak' ? 'warna-ditolak' : 'warna-menunggu');
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="utama"><?= htmlspecialchars($p['nama_user']) ?></span>
                                                <span class="sub">NIM <?= htmlspecialchars($p['nim']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($p['nama_divisi']) ?></td>
                                            <td><?= htmlspecialchars($p['tanggal_pendaftaran'] ?? '-') ?></td>
                                            <td><span class="status-mini <?= $kelasWarna ?>"><?= ucfirst(htmlspecialchars($st)) ?></span></td>
                                            <td class="catatan-lap"><?= htmlspecialchars(($p['catatan'] ?? '') !== '' ? $p['catatan'] : '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
    <script>
        // Export daftar pendaftar event terpilih ke file CSV (Excel)
        document.getElementById('unduh-csv')?.addEventListener('click', function () {
            const rows = document.querySelectorAll('#tabel-pendaftar-laporan tr');
            if (!rows.length) return;
            const csv = [];
            rows.forEach(row => {
                const cols = Array.from(row.querySelectorAll('th, td')).map(col => {
                    const text = col.innerText.replace(/\s+/g, ' ').trim().replace(/"/g, '""');
                    return `"${text}"`;
                });
                csv.push(cols.join(','));
            });
            const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'laporan-pendaftar.csv';
            a.click();
            URL.revokeObjectURL(url);
        });
    </script>
</body>

</html>