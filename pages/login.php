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
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>

    <?php if ($sukses) { ?>
        <p style="color:green;"><?= htmlspecialchars($sukses) ?></p>
    <?php } ?>

    <?php if ($error) { ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form action="../proses/proses_login.php" method="POST">
        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Daftar</a></p>
</body>
</html>