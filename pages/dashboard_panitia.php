<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'panitia') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Panitia</title>
</head>

<body>
    <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama_user']) ?>!</h1>
    <p>Ini adalah halaman Dashboard khusus Panitia.</p>

    <br><a href="pendaftar.php">Lihat Pendaftar</a>
    <br><a href="../proses/logout.php">Logout</a>
</body>