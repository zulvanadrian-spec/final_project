<?php
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'panitia') {
    header("Location: login.php");
    exit;
}
?>