<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

echo "Halo, " . htmlspecialchars($_SESSION['nama']);