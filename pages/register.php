<?php
require_once __DIR__ . '/../autoload.php';
session_start();

// pesan error dari proses_register.php (kalau ada), tampil sekali lalu dihapus
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <main class="login-halaman">
        <div class="login-kartu">

            <div class="login-sisi">
                <h2>Mulai perjalananmu bersama kami</h2>
                <p>Buat akun untuk mendaftar ke berbagai kepanitiaan kampus dan memantau status pendaftaranmu di satu
                    tempat.</p>
            </div>

            <form class="login-form" id="form-daftar" action="../proses/proses_register.php" method="post" enctype="multipart/form-data">
                <h1>Daftar</h1>
                <p class="sub">Isi data dirimu dengan benar.</p>

                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #b91c1c; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <fieldset class="peran">
                    <legend>Daftar sebagai</legend>

                    <label class="peran-opsi">
                        <input type="radio" name="peran" value="mahasiswa" checked>
                        <span>
                            <h4>Mahasiswa</h4>
                            <small>Mendaftar ke kepanitiaan</small>
                        </span>
                    </label>

                    <label class="peran-opsi">
                        <input type="radio" name="peran" value="rekruter">
                        <span>
                            <strong>Rekruter</strong>
                            <small>Perlu kode dari admin</small>
                        </span>
                    </label>
                </fieldset>

                <div class="kolom">
                    <label for="nama">Nama lengkap</label>
                    <input type="text" id="nama" name="nama" placeholder="Nama sesuai KTM" required>
                </div>

                <div class="dua-kolom">
                    <div class="kolom" id="kolom-nim">
                        <label for="nim">NIM</label>
                        <input type="text" id="nim" name="nim" placeholder="Nomor induk" required>
                    </div>
                    <div class="kolom">
                        <label for="prodi">Program studi</label>
                        <input type="text" id="prodi" name="prodi" placeholder="Contoh: Informatika" required>
                    </div>
                </div>

                <div class="kolom">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="nama@email.com" required>
                </div>

                <div class="kolom">
                    <label for="password">Password</label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" placeholder="Minimal 8 karakter"
                            minlength="8" required>
                        <button type="button" class="lihat" id="lihat" aria-label="Tampilkan password">Lihat</button>
                    </div>
                </div>

                <div class="kolom">
                    <label for="konfirmasi">Ulangi password</label>
                    <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ketik ulang password"
                        required>
                    <p class="pesan-error" id="pesan-error"></p>
                </div>

                <div class="kolom" id="kolom-berkas" hidden>
                    <label for="berkas">Bukti verifikasi rekruter</label>
                    <input type="file" id="berkas" name="berkas" accept="image/png,image/jpeg,application/pdf">
                    <p class="petunjuk">Unggah SK kepanitiaan atau surat tugas (PNG, JPG, atau PDF, maksimal 2 MB).
                        Akunmu aktif setelah admin memeriksanya.</p>
                    <p class="pesan-error" id="error-berkas" hidden></p>
                </div>

                <button type="submit" class="btn-submit">Buat Akun</button>

                <p class="pindah">Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </form>

        </div>
    </main>
    <script src="../js/script.js"></script>

</body>

</html>