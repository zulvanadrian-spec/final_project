<?php

namespace pbo_FP\Models;
use pbo_FP\Core\Database;

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

    public static function semuaAkun() {
    $db = Database::getConnection();
    $stmt = $db->query(
        "SELECT u.id_user, u.nama_user, u.email,
                CASE WHEN a.id_admin IS NOT NULL THEN 'admin'
                     WHEN p.id_panitia IS NOT NULL THEN 'panitia'
                     WHEN m.id_mahasiswa IS NOT NULL THEN 'mahasiswa'
                     ELSE '-' END AS role
         FROM user u
         LEFT JOIN admin a ON a.id_user = u.id_user
         LEFT JOIN panitia p ON p.id_user = u.id_user
         LEFT JOIN mahasiswa m ON m.id_user = u.id_user
         ORDER BY u.id_user"
    );
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public static function buatAkunPanitia($nama, $email, $password) {
    $db = Database::getConnection();

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("INSERT INTO user (nama_user, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $email, User::hashPassword($password)]);

        $stmt = $db->prepare("INSERT INTO panitia (id_user) VALUES (?)");
        $stmt->execute([$db->lastInsertId()]);

        $db->commit();
    } catch (\PDOException $e) {
        $db->rollBack();
        throw $e;
    }
}

    public static function jadikanPanitia($id_user) {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO panitia (id_user)
            SELECT m.id_user FROM mahasiswa m
            WHERE m.id_user = ?
            AND NOT EXISTS (SELECT 1 FROM panitia p WHERE p.id_user = m.id_user)"
        );
        $stmt->execute([$id_user]);
        return $stmt->rowCount() > 0;
    }
}