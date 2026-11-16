<?php

namespace pbo_FP\Models;
use pbo_FP\Core\Database;

class User {
    protected $id_user;
    protected $nama_user;
    protected $email;
    protected $password;

    public function __construct($id_user, $nama_user, $email, $password){
        $this->id_user   = $id_user;
        $this->nama_user = $nama_user;
        $this->email     = $email;
        $this->password  = $password;
    }

    public function login(){
        return "User berhasil melakukan login";
    }

    public function logout(){
        return "User berhasil logout";
    }

    // akses nama_user
    public function getNamaUser() {
        return $this->nama_user;
    }

    // akses email
    public function getEmail() {
        return $this->email;
    }

    public function getIdUser() {
    return $this->id_user;
    }

    // password sengaja TIDAK dikasih getter biasa
    public function verifyPassword($input) {
        return password_verify($input, $this->password);
    }

    public static function autentikasi($email, $password) {
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    $user = new User($row['id_user'], $row['nama_user'], $row['email'], $row['password']);

    return $user->verifyPassword($password) ? $user : null;
    }

    public static function hashPassword($plain) {
        return password_hash($plain, PASSWORD_DEFAULT);
}
}