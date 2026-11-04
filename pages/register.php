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
    <title>Register</title>
</head>
<body>
    <h2>Daftar Akun Mahasiswa</h2>

    <?php if ($error) { ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form action="../proses/proses_register.php" method="POST">
        <label>Nama</label><br>
        <input type="text" name="nama_user" required><br><br>

        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>NIM</label><br>
        <input type="text" name="nim" required><br><br>

        <label>Password</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="login.php">Login</a></p>
</body>
</html>