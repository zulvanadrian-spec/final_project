<?php

require_once __DIR__ . '/autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\User;
use pbo_FP\Models\Mahasiswa;

// konek ke database
$db = Database::getConnection();
echo "Konek ke database<br>";

// (A) ambil dari database, bikin jadi objek
$stmt = $db->prepare(
    "SELECT u.id_user, u.nama_user, u.email, u.password, m.id_mahasiswa, m.nim
     FROM user u
     JOIN mahasiswa m ON m.id_user = u.id_user
     WHERE u.email = ?"
);
$stmt->execute(["zulvan@gmail.com"]);
$row = $stmt->fetch(\PDO::FETCH_ASSOC);

$mhs = new Mahasiswa(
    $row['id_user'],
    $row['nama_user'],
    $row['email'],
    $row['password'],
    $row['id_mahasiswa'],
    $row['nim']
);

echo $mhs->getNamaUser() . "<br>";
var_dump($mhs->verifyPassword("rahasia123"));   // bool(true)