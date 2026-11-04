<?php

namespace pbo_FP\Models;

class Panitia extends User { // extends berarti Panitia itu sejenis dengan User / punya semua yg dimiliki User
    private $id_panitia;

    public function __construct($id_user, $nama_user, $email, $password, $id_panitia) {
        parent::__construct($id_user, $nama_user, $email, $password);
        $this->id_panitia = $id_panitia;
    }

    public function getIdPanitia() {
        return $this->id_panitia;
    }
}