<?php

namespace pbo_FP\Models;
use pbo_FP\Core\Database;

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

    // function untuk panitia
    public static function untukPanitia($id_user) {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT p.id_pendaftaran, p.tanggal_pendaftaran, p.status_pendaftaran,
                    u.nama_user, m.nim, d.nama_divisi, e.nama_event
            FROM pendaftaran p
            JOIN mahasiswa m ON m.id_mahasiswa = p.id_mahasiswa
            JOIN user u ON u.id_user = m.id_user
            JOIN divisi d ON d.id_divisi = p.id_divisi
            JOIN event e ON e.id_event = d.id_event
            JOIN panitia pa ON pa.id_panitia = e.id_panitia
            WHERE pa.id_user = ?
            ORDER BY p.tanggal_pendaftaran DESC"
        );
        $stmt->execute([$id_user]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // function untuk mahasiswa
    public static function untukMahasiswa($id_user) {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT p.tanggal_pendaftaran, p.status_pendaftaran,
                    d.nama_divisi, e.nama_event, r.catatan
            FROM pendaftaran p
            JOIN mahasiswa m ON m.id_mahasiswa = p.id_mahasiswa
            JOIN divisi d ON d.id_divisi = p.id_divisi
            JOIN event e ON e.id_event = d.id_event
            LEFT JOIN review r ON r.id_pendaftaran = p.id_pendaftaran
            WHERE m.id_user = ?
            ORDER BY p.tanggal_pendaftaran DESC"
        );
        $stmt->execute([$id_user]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // panitia nerima/nolak -> status berubah lewat method, bukan diubah dari luar
    public function ubahStatus($status_baru) {
        $this->status_pendaftaran = $status_baru;
    }
}