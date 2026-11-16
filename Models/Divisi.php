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
}