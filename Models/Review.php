<?php

namespace pbo_FP\Models;

class Review {
    private $id_review;
    private $id_pendaftaran;
    private $id_rekruter;
    private $keputusan;
    private $catatan;
    private $waktu_review;

    public function __construct($id_review, $id_pendaftaran, $id_rekruter, $keputusan, $catatan, $waktu_review) {
        $this->id_review = $id_review;
        $this->id_pendaftaran = $id_pendaftaran;
        $this->id_rekruter = $id_rekruter;
        $this->keputusan = $keputusan;
        $this->catatan = $catatan;
        $this->waktu_review = $waktu_review;
    }

    public function getIdReview(){
        return $this->id_review;
    }
    public function getIdPendaftaran(){
        return $this->id_pendaftaran;
    }
    public function getIdRekruter(){
        return $this->id_rekruter;
    }
    public function getKeputusan(){
        return $this->keputusan;
    }
    public function getCatatan(){
        return $this->catatan;
    }
    public function getWaktuReview(){
        return $this->waktu_review;
    }
}