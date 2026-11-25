<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Admin;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$halaman = 'akun';
$semuaAkun = Admin::semuaAkun();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pendaftaran.css">
    <link rel="stylesheet" href="../css/kelola-event.css">
    <link rel="stylesheet" href="../css/kelola-akun.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar-admin.php'; ?>

    <main class="halaman-pendaftaran halaman-kelola halaman-akun">
        <section class="isi-dashboard">

            <div class="kepala-halaman">
                <div>
                    <h1>Daftar akun</h1>
                    <p>Seluruh pengguna yang terdaftar di dalam sistem.</p>
                </div>
            </div>

            <div class="filter-kelola">
                <input type="search" id="cari" placeholder="Cari nama atau email">
                <select id="f-peran">
                    <option value="">Semua peran</option>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="rekruter">Rekruter</option>
                    <option value="calon_rekruter">Menunggu Verifikasi</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <p class="jumlah" id="jumlah"></p>

            <div class="tabel-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pengguna</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="isi-tabel">
                        <?php foreach ($semuaAkun as $ak): ?>
                            <?php
                                $idU   = (int) $ak['id_user'];
                                $role  = $ak['role'];
                                $isMe  = ($idU === (int) $_SESSION['id_user']);

                                if ($role === 'admin') {
                                    $labelRole = 'Admin';
                                    $badge     = 'peran-admin';
                                    $statusTxt = 'Aktif';
                                    $statusCls = 'aktif';
                                } elseif ($role === 'rekruter') {
                                    $labelRole = 'Rekruter';
                                    $badge     = 'peran-panitia';
                                    $statusTxt = 'Aktif';
                                    $statusCls = 'aktif';
                                } elseif ($role === 'mahasiswa') {
                                    $labelRole = 'Mahasiswa';
                                    $badge     = 'peran-mahasiswa';
                                    $statusTxt = 'Aktif';
                                    $statusCls = 'aktif';
                                } else {
                                    $labelRole = 'Calon Rekruter';
                                    $badge     = 'peran-panitia';
                                    $stPeng    = $ak['status_pengajuan'] ?? 'menunggu';
                                    $statusTxt = $stPeng === 'ditolak' ? 'Ditolak' : 'Menunggu verifikasi';
                                    $statusCls = $stPeng === 'ditolak' ? 'nonaktif' : 'menunggu';
                                }
                            ?>
                            <tr data-nama="<?= htmlspecialchars($ak['nama_user']) ?>"
                                data-email="<?= htmlspecialchars($ak['email']) ?>"
                                data-peran="<?= htmlspecialchars($role) ?>">
                                <td>#<?= $idU ?></td>
                                <td>
                                    <span class="utama">
                                        <?= htmlspecialchars($ak['nama_user']) ?>
                                        <?php if ($isMe): ?><span class="penanda">kamu</span><?php endif; ?>
                                    </span>
                                    <span class="sub"><?= htmlspecialchars($ak['email']) ?></span>
                                </td>
                                <td><span class="peran-badge <?= $badge ?>"><?= $labelRole ?></span></td>
                                <td><span class="status <?= $statusCls ?>"><?= $statusTxt ?></span></td>
                                <td class="sel-aksi">
                                    <?php if ($statusCls === 'menunggu'): ?>
                                        <a href="verifikasi.php" class="aksi aksi-periksa">Periksa SK</a>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 13px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="kosong" id="kosong" hidden>Tidak ada akun yang cocok.</p>

        </section>
    </main>

    <script src="../js/dashboard.js"></script>
    <script>
        const cari = document.getElementById('cari');
        const fPeran = document.getElementById('f-peran');
        const baris = [...document.querySelectorAll('#isi-tabel tr')];
        const jumlah = document.getElementById('jumlah');
        const kosong = document.getElementById('kosong');

        function saring() {
            const kata = cari.value.trim().toLowerCase();
            let tampil = 0;
            baris.forEach(tr => {
                const cocok = (tr.dataset.nama + ' ' + tr.dataset.email).toLowerCase().includes(kata) &&
                              (!fPeran.value || tr.dataset.peran === fPeran.value);
                tr.hidden = !cocok;
                if (cocok) tampil++;
            });
            jumlah.textContent = `Menampilkan ${tampil} dari ${baris.length} akun`;
            kosong.hidden = tampil !== 0;
        }
        cari.addEventListener('input', saring);
        fPeran.addEventListener('change', saring);
        saring();
    </script>
</body>
</html>