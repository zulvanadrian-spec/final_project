<?php
require_once __DIR__ . '/../autoload.php';
session_start();

$error = $_SESSION['error'] ?? null;
$sukses = $_SESSION['sukses'] ?? null;
unset($_SESSION['error'], $_SESSION['sukses']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rekruterin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <main class="login-halaman">
        <div class="login-kartu">

            <div class="login-sisi">
                <h2>Selamat datang!</h2>
                <p>Masuk untuk melanjutkan pendaftaran dan memantau status kepanitiaanmu.</p>
            </div>

            <form class="login-form" action="../proses/proses_login.php" method="post">
                <h1>Login</h1>
                <p class="sub">Masukkan akun yang sudah kamu daftarkan.</p>

                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #b91c1c; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($sukses): ?>
                    <div style="background: #dcfce7; color: #15803d; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
                        <?= htmlspecialchars($sukses) ?>
                    </div>
                <?php endif; ?>

                <div class="kolom">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="nama@email.com" required>
                </div>

                <div class="kolom">
                    <label for="password">Password</label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                        <button type="button" class="lihat" id="lihat" aria-label="Tampilkan password">Lihat</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Masuk</button>

                <p class="pindah">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            </form>

        </div>
    </main>

    <script>
        const input = document.getElementById("password");
        const tombol = document.getElementById("lihat");

        tombol.addEventListener("click", () => {
            const tersembunyi = input.type === "password";
            input.type = tersembunyi ? "text" : "password";
            tombol.textContent = tersembunyi ? "Sembunyi" : "Lihat";
        });
    </script>
</body>

</html>