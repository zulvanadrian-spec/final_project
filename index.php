<?php

session_start();

$tujuan = [
    'mahasiswa' => 'pages/dashboard_mahasiswa.php',
    'rekruter'  => 'pages/dashboard_rekruter.php',
    'admin'     => 'pages/dashboard_admin.php',
];

if (isset($_SESSION['role']) && isset($tujuan[$_SESSION['role']])) {
    header("Location: " . $tujuan[$_SESSION['role']]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYLVARA</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/lobby.css">
</head>

<body>
    <header class="navbar">
        <a href="index.php" class="logo">
            <img src="images/logo_sylvara.png" alt="logo">
        </a>

        <div class="auth">
            <a href="pages/login.php" class="btn-login">Login</a>
            <a href="pages/register.php" class="btn-daftar">Daftar</a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-isi">
                <h1>Bergabunglah Jadi Bagian Dari Perubahan!</h1>
                <p>Rekrutmen dan pendaftaran anggota kepanitiaan disini. Wujudkan, pengalaman bangun relasi, dan
                    kembangkan diri bersama kami.</p>
                <a href="pages/register.php" class="btn-hero">Mulai Sekarang</a>
            </div>
        </section>

        <section class="tentang" id="tentang">
            <div class="tentang-wadah">
                <div class="tentang-judul">
                    <span class="label">Tentang Kami</span>
                    <h2>Wadah mahasiswa untuk berkarya dan berkembang</h2>
                </div>

                <div class="tentang-isi">
                    <p>
                        Rekruterin adalah platform rekrutmen kepanitiaan kampus. Kami
                        mempertemukan mahasiswa yang ingin berkontribusi dengan acara-acara
                        yang membutuhkan tim terbaik.
                    </p>
                    <hr>
                    <p>
                        Dari pendaftaran sampai seleksi, semuanya dibuat lebih mudah dan
                        transparan, supaya kamu bisa fokus membangun pengalaman, relasi, dan
                        kemampuan baru.
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>

</html>