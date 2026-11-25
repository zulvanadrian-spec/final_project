<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$halaman = 'verifikasi';
$db = Database::getConnection();

$semuaPengajuan = $db->query("
    SELECT pp.id_pengajuan, pp.tanggal_pengajuan, pp.status_pengajuan, pp.berkas, pp.catatan, u.nama_user, u.email
    FROM pengajuan_rekruter pp
    JOIN user u ON u.id_user = pp.id_user
    ORDER BY FIELD(pp.status_pengajuan, 'menunggu', 'disetujui', 'ditolak'), pp.tanggal_pengajuan DESC
")->fetchAll(\PDO::FETCH_ASSOC);

$hitung = ['menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0];
foreach ($semuaPengajuan as $row) {
    $st = $row['status_pengajuan'];
    if (isset($hitung[$st])) $hitung[$st]++;
}
$totalSemua = count($semuaPengajuan);

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Panitia - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/kelola-event.css">
    <link rel="stylesheet" href="../css/kelola-akun.css">
    <link rel="stylesheet" href="../css/verifikasi.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar-admin.php'; ?>

    <main class="halaman-pendaftaran halaman-kelola halaman-verifikasi">
        <section class="isi-dashboard">

            <div class="kepala-halaman">
                <div>
                    <h1>Verifikasi panitia / rekruter</h1>
                    <p>Periksa berkas SK pendaftar yang ingin menjadi rekruter, lalu setujui atau tolak beserta catatannya.</p>
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

            <div class="baris-filter">
                <div class="tab-status" role="group" aria-label="Filter status verifikasi">
                    <button type="button" class="tab" data-status="menunggu" aria-pressed="true">
                        Menunggu <span class="hitung"><?= $hitung['menunggu'] ?></span>
                    </button>
                    <button type="button" class="tab" data-status="disetujui" aria-pressed="false">
                        Disetujui <span class="hitung"><?= $hitung['disetujui'] ?></span>
                    </button>
                    <button type="button" class="tab" data-status="ditolak" aria-pressed="false">
                        Ditolak <span class="hitung"><?= $hitung['ditolak'] ?></span>
                    </button>
                    <button type="button" class="tab" data-status="" aria-pressed="false">
                        Semua <span class="hitung"><?= $totalSemua ?></span>
                    </button>
                </div>
                <input type="search" id="cari" placeholder="Cari nama atau email">
            </div>

            <div class="tabel-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Pemohon</th>
                            <th>Berkas SK</th>
                            <th>Tanggal daftar</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isi-tabel">
                        <?php foreach ($semuaPengajuan as $p): ?>
                            <?php
                                $idPeng  = (int) $p['id_pengajuan'];
                                $st      = $p['status_pengajuan'];
                                $berkas  = $p['berkas'] ?? '';
                                $catatan = trim($p['catatan'] ?? '');
                                $kelasSt = $st === 'disetujui' ? 'diterima' : ($st === 'ditolak' ? 'ditolak' : 'menunggu');
                            ?>
                            <tr class="<?= $st === 'ditolak' ? 'ditolak' : '' ?>"
                                data-nama="<?= htmlspecialchars($p['nama_user']) ?>"
                                data-email="<?= htmlspecialchars($p['email']) ?>"
                                data-status="<?= htmlspecialchars($st) ?>">
                                <td>
                                    <span class="utama"><?= htmlspecialchars($p['nama_user']) ?></span>
                                    <span class="sub"><?= htmlspecialchars($p['email']) ?></span>
                                </td>
                                <td>
                                    <?php if ($berkas !== ''): ?>
                                        <a href="../uploads/<?= urlencode($berkas) ?>" target="_blank" class="aksi aksi-detail">Lihat Berkas SK</a>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 13px;">Tidak ada berkas</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['tanggal_pengajuan']) ?></td>
                                <td>
                                    <span class="status <?= $kelasSt ?>"><?= ucfirst(htmlspecialchars($st)) ?></span>
                                </td>
                                <td>
                                    <span class="keterangan"><?= $catatan !== '' ? htmlspecialchars($catatan) : '-' ?></span>
                                </td>
                                <td class="sel-aksi">
                                    <?php if ($st === 'menunggu'): ?>
                                        <button type="button" class="aksi aksi-setuju btn-buka-dialog"
                                            data-id="<?= $idPeng ?>"
                                            data-nama="<?= htmlspecialchars($p['nama_user']) ?>"
                                            data-email="<?= htmlspecialchars($p['email']) ?>"
                                            data-keputusan="disetujui">
                                            Setujui
                                        </button>
                                        <button type="button" class="aksi aksi-tolak btn-buka-dialog"
                                            data-id="<?= $idPeng ?>"
                                            data-nama="<?= htmlspecialchars($p['nama_user']) ?>"
                                            data-email="<?= htmlspecialchars($p['email']) ?>"
                                            data-keputusan="ditolak">
                                            Tolak
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 13px;">Sudah diputuskan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="kosong" id="kosong" hidden>Tidak ada permohonan yang cocok.</p>

        </section>
    </main>

    <dialog id="dialog-keputusan" class="dialog-akun">
        <form action="../proses/proses_akun.php" method="POST" id="form-keputusan">
            <input type="hidden" name="aksi" value="putuskan_pengajuan">
            <input type="hidden" name="id_pengajuan" id="input-id-pengajuan">
            <input type="hidden" name="keputusan" id="input-keputusan">

            <h2 id="dialog-judul">Putuskan permohonan</h2>
            <p class="dialog-nama" id="dialog-pemohon"></p>

            <label for="catatan" class="label-alasan" id="label-catatan">Catatan</label>
            <textarea id="catatan" name="catatan" rows="4" maxlength="300"></textarea>
            <small class="petunjuk" id="petunjuk-catatan"></small>

            <p class="pesan-error" id="galat-catatan" hidden></p>

            <div class="dialog-aksi">
                <button type="button" class="btn-batal" data-tutup>Batal</button>
                <button type="submit" id="btn-konfirmasi">Simpan</button>
            </div>
        </form>
    </dialog>

    <script src="../js/dashboard.js"></script>
    <script>
        const tabs = document.querySelectorAll('.tab');
        const cari = document.getElementById('cari');
        const baris = [...document.querySelectorAll('#isi-tabel tr')];
        const kosong = document.getElementById('kosong');
        let statusAktif = 'menunggu';

        function saring() {
            const kata = cari.value.trim().toLowerCase();
            let tampil = 0;
            baris.forEach(tr => {
                const cocok = (!statusAktif || tr.dataset.status === statusAktif) &&
                              (tr.dataset.nama + ' ' + tr.dataset.email).toLowerCase().includes(kata);
                tr.hidden = !cocok;
                if (cocok) tampil++;
            });
            kosong.hidden = tampil !== 0;
        }

        tabs.forEach(t => {
            t.addEventListener('click', () => {
                statusAktif = t.dataset.status;
                tabs.forEach(x => x.setAttribute('aria-pressed', String(x === t)));
                saring();
            });
        });
        cari.addEventListener('input', saring);
        saring();

        // Dialog Setujui / Tolak dengan catatan
        const dlg = document.getElementById('dialog-keputusan');
        const formKeputusan = document.getElementById('form-keputusan');
        const inputId = document.getElementById('input-id-pengajuan');
        const inputKeputusan = document.getElementById('input-keputusan');
        const dialogJudul = document.getElementById('dialog-judul');
        const dialogPemohon = document.getElementById('dialog-pemohon');
        const labelCatatan = document.getElementById('label-catatan');
        const textareaCatatan = document.getElementById('catatan');
        const petunjukCatatan = document.getElementById('petunjuk-catatan');
        const galatCatatan = document.getElementById('galat-catatan');
        const btnKonfirmasi = document.getElementById('btn-konfirmasi');

        document.querySelectorAll('.btn-buka-dialog').forEach(btn => {
            btn.addEventListener('click', () => {
                const keputusan = btn.dataset.keputusan;
                inputId.value = btn.dataset.id;
                inputKeputusan.value = keputusan;
                dialogPemohon.textContent = `${btn.dataset.nama} (${btn.dataset.email})`;
                textareaCatatan.value = '';
                galatCatatan.hidden = true;

                if (keputusan === 'ditolak') {
                    dialogJudul.textContent = 'Tolak permohonan';
                    labelCatatan.textContent = 'Alasan penolakan (wajib)';
                    textareaCatatan.placeholder = 'Contoh: Berkas tidak terbaca atau bukan surat tugas kepanitiaan.';
                    petunjukCatatan.textContent = 'Alasan ini akan ditampilkan kepada pemohon saat mencoba login.';
                    btnKonfirmasi.textContent = 'Tolak permohonan';
                    btnKonfirmasi.className = 'btn-tolak';
                } else {
                    dialogJudul.textContent = 'Setujui permohonan';
                    labelCatatan.textContent = 'Catatan persetujuan (opsional)';
                    textareaCatatan.placeholder = 'Contoh: Berkas SK valid, silakan mulai kelola event.';
                    petunjukCatatan.textContent = 'Pemohon akan langsung aktif sebagai Rekruter setelah disetujui.';
                    btnKonfirmasi.textContent = 'Setujui permohonan';
                    btnKonfirmasi.className = 'btn-ya';
                }

                dlg.showModal();
                textareaCatatan.focus();
            });
        });

        formKeputusan.addEventListener('submit', (e) => {
            if (inputKeputusan.value === 'ditolak' && textareaCatatan.value.trim() === '') {
                e.preventDefault();
                galatCatatan.textContent = 'Alasan penolakan wajib diisi.';
                galatCatatan.hidden = false;
            }
        });

        document.querySelectorAll('[data-tutup]').forEach(b => {
            b.addEventListener('click', () => b.closest('dialog').close());
        });
    </script>
</body>
</html>