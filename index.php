<?php
session_start();

$tujuan = [
    'mahasiswa' => 'pages/dashboard_mahasiswa.php',
    'panitia'   => 'pages/dashboard_panitia.php',
    'admin'     => 'pages/dashboard_admin.php',
];

$role = $_SESSION['role'] ?? '';

header("Location: " . ($tujuan[$role] ?? 'pages/login.php'));
exit;