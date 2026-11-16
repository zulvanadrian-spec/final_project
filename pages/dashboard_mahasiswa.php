<?php
session_start();

// Cek apakah user sudah login dan benar-benar mahasiswa
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mahasiswa</title>
</head>
<body>
    <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama_user']) ?>!</h1>
    <p>Ini adalah halaman Dashboard khusus Mahasiswa.</p>
    
    <a href="login.php">Logout</a>
</body>
</html>