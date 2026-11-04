<?php

require_once __DIR__ . '/autoload.php';

use pbo_FP\Models\Mahasiswa;
use pbo_FP\Models\User;          
use pbo_FP\Models\Pendaftaran;
use pbo_FP\Models\Review;
use pbo_FP\Models\Event;

// classs Mahasiswa.php
$mhs = new Mahasiswa(
    1,
    "zulvan",
    "zulvan@gmail.com",
    User::hashPassword("rahasia123"),   // simpan dalam bentuk hash
    1,
    "25416255201061"
);

// dilarang, hanya percobaan akses atribut private
echo "nim mahasiswa berhasil diakses: ".$mhs->getNim() ."<br>";
echo "id_mahasiswa berhasil diakses: ".$mhs->getIdMahasiswa();

// tes password
var_dump($mhs->verifyPassword("rahasia123"));  // bool(true)
var_dump($mhs->verifyPassword("salah"));       // bool(false)

// tes Models pendaftaran
$daftar = new Pendaftaran(
    1,      // id_pendaftaran
    1,      // id_mahasiswa
    1,      // id_divisi
    "2026-11-02 10:00:00" // tanggal_pendaftaran
);

echo $daftar->getStatusPendaftaran() . "<br>";   // menunggu
$daftar->ubahStatus("diterima");
echo $daftar->getStatusPendaftaran() . "<br>";   // diterima

// tes Models Review
$rev = new Review(
    1,            // id_review
    1,            // id_pendaftaran
    1,            // id_panitia
    "diterima",   // keputusan
    "Aktif dan komunikatif",    // catatan
    "2026-11-02 14:30:00"       // waktu_review
);
echo $rev->getKeputusan();      // diterima by history