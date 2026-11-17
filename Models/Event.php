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

    public static function cari($id_event) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM event WHERE id_event = ?");
        $stmt->execute([$id_event]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
    }
    return new Event(
        $row['id_event'], $row['id_panitia'], $row['nama_event'],
        $row['tanggal_event'], $row['status_event']
        );
    }

    public static function milikPanitia($id_user) {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT e.* FROM event e
            JOIN panitia p ON p.id_panitia = e.id_panitia
            WHERE p.id_user = ?
            ORDER BY e.tanggal_event ASC"
        );
        $stmt->execute([$id_user]);

        $hasil = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $hasil[] = new Event(
                $row['id_event'], $row['id_panitia'], $row['nama_event'],
                $row['tanggal_event'], $row['status_event']
            );
        }
        return $hasil;
    }

    public static function tambahEvent($id_user, $nama_event, $tanggal_event, $status_event) {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO event (id_panitia, nama_event, tanggal_event, status_event)
            SELECT id_panitia, ?, ?, ? FROM panitia WHERE id_user = ?"
        );
        $stmt->execute([$nama_event, $tanggal_event, $status_event, $id_user]);
        return $stmt->rowCount() > 0;
        }
    }