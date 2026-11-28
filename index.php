<?php

session_start();

$tujuan = [
    'mahasiswa' => 'pages/dashboard_mahasiswa.php',
    'rekruter' => 'pages/dashboard_rekruter.php',
    'admin' => 'pages/dashboard_admin.php',
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=Manrope:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                <h1 id="headline">
                    Buat event.<br />Bangun cerita.<br />Mulai dari sini.
                </h1>
                <p>
                    Punya ide, energi, atau sekadar ingin mencoba hal baru? Temukan tim
                    kepanitiaan yang cocok dan ciptakan momen kampus bareng Sylvara.
                </p>
                <a href="pages/register.php" class="btn-hero">Mulai Sekarang &rarr;</a>
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
                        Sylvara adalah platform rekrutmen kepanitiaan kampus. Kami
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

        <img src="./images/group_lobby.png" style="display: block; margin: 0 auto; max-width: 100%; height: auto;">

        <section class="section-divisi">
            <div class="divisi-header">
                <span class="divisi-eyebrow">BUKAN HANYA MENJADI PENONTON</span>
                <h2 class="divisi-title">Event terbaik kampus dimulai dari orang-orang di baliknya.</h2>
                <p class="divisi-subtitle">
                    Gabung ke kepanitiaan, coba peran baru, dan membuat sesuatu yang akan dikenang bareng teman-teman
                    kampusmu.
                </p>
            </div>

            <div class="divisi-grid">
                <!-- Kartu 1 -->
                <div class="divisi-card">
                    <div class="divisi-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L14.8 9.2L22 12L14.8 14.8L12 22L9.2 14.8L2 12L9.2 9.2L12 2Z" />
                        </svg>
                    </div>
                    <h3>Kreatif &amp; Publikasi</h3>
                    <p>Rancang konsep visual, konten, dan cerita yang bikin orang tertarik datang.</p>
                </div>

                <!-- Kartu 2 -->
                <div class="divisi-card">
                    <div class="divisi-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                        </svg>
                    </div>
                    <h3>Acara &amp; Operasional</h3>
                    <p>Atur jalannya acara, koordinasi lapangan, dan pastikan semua momen lancar.</p>
                </div>

                <!-- Kartu 3 -->
                <div class="divisi-card">
                    <div class="divisi-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round">
                            <path d="M12 3v18M3 12h18M5.6 5.6l12.8 12.8M18.4 5.6L5.6 18.4" />
                        </svg>
                    </div>
                    <h3>Relasi &amp; Sponsorship</h3>
                    <p>Bangun koneksi, kerja sama, dan dukungan untuk mewujudkan ide besar.</p>
                </div>
            </div>
        </section>

    </main>
</body>

</html>