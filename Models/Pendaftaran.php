<?php

namespace pbo_FP\Models;

class Pendaftaran {
    private $id_pendaftaran;
    private $id_mahasiswa;
    private $id_divisi;
    private $tanggal_pendaftaran;
    private $status_pendaftaran;

    public function __construct($id_pendaftaran, $id_mahasiswa, $id_divisi, $tanggal_pendaftaran, $status_pendaftaran = "menunggu") {
        $this->id_pendaftaran = $id_pendaftaran;
        $this->id_mahasiswa = $id_mahasiswa;
        $this->id_divisi = $id_divisi;
        $this->tanggal_pendaftaran = $tanggal_pendaftaran;
        $this->status_pendaftaran = $status_pendaftaran;
    }

    public function getIdPendaftaran() {
        return $this->id_pendaftaran;
    }

    public function getIdMahasiswa() {
        return $this->id_mahasiswa;
    }

    public function getIdDivisi() {
        return $this->id_divisi;
    }

    public function getTanggalPendaftaran() {
        return $this->tanggal_pendaftaran;
    }

    public function getStatusPendaftaran() {
        return $this->status_pendaftaran;
    }

    // panitia nerima/nolak -> status berubah lewat method, bukan diubah dari luar
    public function ubahStatus($status_baru) {
        $this->status_pendaftaran = $status_baru;
    }
}