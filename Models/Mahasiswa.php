<?php

namespace pbo_FP\Models;

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

}