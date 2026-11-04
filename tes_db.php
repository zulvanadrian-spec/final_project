<?php

require_once __DIR__ . '/autoload.php';

use pbo_FP\Core\Database;
use pbo_FP\Models\User;
use pbo_FP\Models\Mahasiswa;

// konek ke database
$db = Database::getConnection();
echo "Konek ke database<br>";

// (A) insert data dummy, jalanin SEKALI aja lalu komen bagian ini

// 1) insert ke user
$stmt = $db->prepare("INSERT INTO user (nama_user, email, password) VALUES (?, ?, ?)");
$stmt->execute(["Zulvan", "zulvan@gmail.com", User::hashPassword("rahasia123")]);

// 2) ambil id_user yang barusan dibikin MySQL
$id_user = $db->lastInsertId();

// 3) insert ke mahasiswa, pakai id_user tadi
$stmt = $db->prepare("INSERT INTO mahasiswa (id_user, nim) VALUES (?, ?)");
$stmt->execute([$id_user, "25416255201061"]);

// (B) ambil dari database, bikin jadi objek
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

echo $mhs->getNama() . "<br>";
var_dump($mhs->verifyPassword("rahasia123"));   // bool(true)