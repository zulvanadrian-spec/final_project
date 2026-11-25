<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Core\Database;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$halaman = 'profil';
$db = Database::getConnection();

$stmt = $db->prepare("
    SELECT u.nama_user, u.email, m.nim, m.tanggal_lahir, m.jenis_kelamin, m.no_hp, m.prodi, m.angkatan 
    FROM user u 
    JOIN mahasiswa m ON m.id_user = u.id_user 
    WHERE u.id_user = ?
");
$stmt->execute([$_SESSION['id_user']]);
$profil = $stmt->fetch(\PDO::FETCH_ASSOC);

$gender = $profil['jenis_kelamin'] ?? 'laki-laki';

$error  = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/profil.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="halaman-profil">
        <section class="isi-dashboard">
            <form action="../proses/proses_profile.php" method="post" class="profil-form">

                <h2>Profil anda</h2>

                <?php if ($sukses): ?>
                    <div style="background: #dcfce7; color: #15803d; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        <?= htmlspecialchars($sukses) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #b91c1c; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="kolom">
                    <label for="nama">Nama lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($profil['nama_user'] ?? '') ?>" required>
                </div>

                <div class="kolom">
                    <label for="lahir">Tanggal lahir</label>
                    <input type="date" id="lahir" name="lahir" value="<?= htmlspecialchars($profil['tanggal_lahir'] ?? '') ?>">
                </div>

                <div class="kolom">
                    <span class="label-teks">Jenis kelamin</span>
                    <div class="pilihan">
                        <label class="opsi">
                            <input type="radio" name="gender" value="laki-laki" <?= $gender === 'laki-laki' ? 'checked' : '' ?>>
                            <span><i class="titik"></i>Laki-laki</span>
                        </label>
                        <label class="opsi">
                            <input type="radio" name="gender" value="perempuan" <?= $gender === 'perempuan' ? 'checked' : '' ?>>
                            <span><i class="titik"></i>Perempuan</span>
                        </label>
                    </div>
                </div>

                <h2>Detail kontak</h2>

                <div class="kolom">
                    <label for="hp">Nomor HP / WhatsApp</label>
                    <input type="tel" id="hp" name="hp" placeholder="+62 812 3456 7890" value="<?= htmlspecialchars($profil['no_hp'] ?? '') ?>">
                </div>

                <div class="kolom">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($profil['email'] ?? '') ?>" required>
                </div>

                <h2>Detail akademik</h2>

                <div class="kolom">
                    <label for="nim">NIM</label>
                    <input type="text" id="nim" name="nim" value="<?= htmlspecialchars($profil['nim'] ?? '') ?>" required>
                </div>

                <div class="kolom">
                    <label for="prodi">Program studi</label>
                    <input type="text" id="prodi" name="prodi" placeholder="Contoh: Informatika" value="<?= htmlspecialchars($profil['prodi'] ?? '') ?>">
                </div>

                <div class="kolom">
                    <label for="angkatan">Angkatan</label>
                    <input type="text" id="angkatan" name="angkatan" placeholder="Contoh: 2024" value="<?= htmlspecialchars($profil['angkatan'] ?? '') ?>">
                </div>

                <button type="submit" class="btn-simpan">Simpan perubahan</button>
            </form>
        </section>
    </main>

    <script src="../js/dashboard.js"></script>
</body>
</html>