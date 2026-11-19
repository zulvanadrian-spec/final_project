<?php

namespace pbo_FP\Models;

class Rekruter extends User { // extends berarti Rekruter itu sejenis dengan User / punya semua yg dimiliki User
    private $id_rekruter;

    public function __construct($id_user, $nama_user, $email, $password, $id_rekruter) {
        parent::__construct($id_user, $nama_user, $email, $password);
        $this->id_rekruter = $id_rekruter;
    }

    public function getIdRekruter() {
        return $this->id_rekruter;
    }
}