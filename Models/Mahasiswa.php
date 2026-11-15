<?php

namespace pbo_FP\Models;

use pbo_FP\Core\Database;

class Mahasiswa extends User { // artinya Mahasiswa sama seperti User punya semua yg dimiliki User
    private $id_mahasiswa;
    private $nim;

    public function __construct($id_user, $nama_user, $email, $password, $id_mahasiswa, $nim) {
        parent::__construct($id_user, $nama_user, $email, $password);

        $this->id_mahasiswa = $id_mahasiswa;
        $this->nim = $nim;
    }

    // akses nim 'private'
    public function getNim(){
        return $this->nim;
    }

    // akses id_mahasiswa 'private'
    public function getIdMahasiswa(){
        return $this->id_mahasiswa;
    }

    // <-- 2. Tambahkan method static pencarian ini di sebelum kurung tutup class -->
    public static function cari($keyword) {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT u.nama_user, u.email, m.nim 
             FROM mahasiswa m 
             JOIN user u ON m.id_user = u.id_user 
             WHERE u.nama_user LIKE ? OR m.nim LIKE ?"
        );
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}