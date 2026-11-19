<?php

namespace pbo_FP\Models;
use pbo_FP\Core\Database;

class Divisi {
    private $id_divisi;
    private $id_event;
    private $nama_divisi;
    private $kuota;

    public function __construct($id_divisi, $id_event, $nama_divisi, $kuota){
        $this->id_divisi = $id_divisi;
        $this->id_event = $id_event;
        $this->nama_divisi = $nama_divisi;
        $this->kuota = $kuota;

    }

    public function getIdDivisi(){
        return $this->id_divisi;
    }
    public function getIdEvent(){
        return $this->id_event;
    }
    public function getNamaDivisi(){
        return $this->nama_divisi;
    }
    public function getkuota(){
        return $this->kuota;
    }

    public static function byEvent($id_event) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM divisi WHERE id_event = ? ORDER BY nama_divisi");
        $stmt->execute([$id_event]);

        $hasil = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $hasil[] = new Divisi($row['id_divisi'], $row['id_event'], $row['nama_divisi'], $row['kuota']);
        }
        return $hasil;
    }

    public static function milikRekruterIni($id_divisi, $id_user) {
    $db = Database::getConnection();
    $stmt = $db->prepare(
        "SELECT 1 FROM divisi d
         JOIN event e ON e.id_event = d.id_event
         JOIN rekruter p ON p.id_rekruter = e.id_rekruter
         WHERE d.id_divisi = ? AND p.id_user = ?"
    );
    $stmt->execute([$id_divisi, $id_user]);
    return (bool) $stmt->fetchColumn();
}

public static function tambahDivisi($id_event, $id_user, $nama_divisi, $kuota) {
    if (!Event::milikRekruterIni($id_event, $id_user)) {
        return false;
    }
    $db = Database::getConnection();
    $stmt = $db->prepare("INSERT INTO divisi (id_event, nama_divisi, kuota) VALUES (?, ?, ?)");
    $stmt->execute([$id_event, $nama_divisi, $kuota]);
    return true;
}

    public static function ubahDivisi($id_divisi, $id_user, $nama_divisi, $kuota) {
        if (!self::milikRekruterIni($id_divisi, $id_user)) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE divisi SET nama_divisi = ?, kuota = ? WHERE id_divisi = ?");
        $stmt->execute([$nama_divisi, $kuota, $id_divisi]);
        return true;
    }
}