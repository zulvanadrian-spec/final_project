<?php

namespace pbo_FP\Models;

class Admin extends User { // extends berarti Admin itu sejenis dengan User / punya semua yg dimiliki User
    private $id_admin;

    public function __construct($id_user, $nama_user, $email, $password, $id_admin){

        //menitipkan nilai ke induk class yaitu User
        parent::__construct($id_user, $nama_user, $email, $password);
        $this->id_admin = $id_admin;
    }

    public function getIdAdmin(){
        return $this->id_admin;
    }
}