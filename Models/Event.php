<?php

namespace pbo_FP\Models;
use pbo_FP\Core\Database;

class Event {
    private $id_event;
    private $id_panitia;
    private $nama_event;
    private $tanggal_event;
    private $status_event;

    public function __construct($id_event, $id_panitia, $nama_event, $tanggal_event, $status_event) {
        $this->id_event = $id_event;
        $this->id_panitia = $id_panitia;
        $this->nama_event = $nama_event;
        $this->tanggal_event = $tanggal_event;
        $this->status_event = $status_event;
    }

    public function getIdEvent(){
        return $this->id_event;
    }
    public function getIdPanitia(){
        return $this->id_panitia;
    }
    public function getNamaEvent(){
        return $this->nama_event;
    }
    public function getTanggalEvent(){
        return $this->tanggal_event;
    }
    public function getStatusEvent(){
        return $this->status_event;
    }
    
    public static function semua() {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT * FROM event ORDER BY tanggal_event ASC");

    $hasil = [];
    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $hasil[] = new Event(
            $row['id_event'], $row['id_panitia'], $row['nama_event'],
            $row['tanggal_event'], $row['status_event']
        );
    }
    return $hasil;
}
}