<?php

namespace pbo_FP\Models;
use pbo_FP\Core\Database;

class Admin extends User
{ // extends berarti Admin itu sejenis dengan User / punya semua yg dimiliki User
    private $id_admin;

    public function __construct($id_user, $nama_user, $email, $password, $id_admin)
    {

        //menitipkan nilai ke induk class yaitu User
        parent::__construct($id_user, $nama_user, $email, $password);
        $this->id_admin = $id_admin;
    }

    public function getIdAdmin()
    {
        return $this->id_admin;
    }

    public static function semuaAkun()
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT u.id_user, u.nama_user, u.email,
                    CASE WHEN a.id_admin IS NOT NULL THEN 'admin'
                        WHEN p.id_rekruter IS NOT NULL THEN 'rekruter'
                        WHEN m.id_mahasiswa IS NOT NULL THEN 'mahasiswa'
                        ELSE '-' END AS role
            FROM user u
            LEFT JOIN admin a ON a.id_user = u.id_user
            LEFT JOIN rekruter p ON p.id_user = u.id_user
            LEFT JOIN mahasiswa m ON m.id_user = u.id_user
            ORDER BY u.id_user"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function buatAkunRekruter($nama, $email, $password)
    {
        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO user (nama_user, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$nama, $email, User::hashPassword($password)]);

            $stmt = $db->prepare("INSERT INTO rekruter (id_user) VALUES (?)");
            $stmt->execute([$db->lastInsertId()]);

            $db->commit();
        } catch (\PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function jadikanRekruter($id_user)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO rekruter (id_user)
            SELECT m.id_user FROM mahasiswa m
            WHERE m.id_user = ?
            AND NOT EXISTS (SELECT 1 FROM rekruter p WHERE p.id_user = m.id_user)"
        );
        $stmt->execute([$id_user]);
        return $stmt->rowCount() > 0;
    }

    public static function pengajuanMenunggu()
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT pp.id_pengajuan, pp.tanggal_pengajuan, u.nama_user, u.email
            FROM pengajuan_rekruter pp
            JOIN user u ON u.id_user = pp.id_user
            WHERE pp.status_pengajuan = 'menunggu'
            ORDER BY pp.tanggal_pengajuan ASC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function putuskanPengajuan($id_pengajuan, $keputusan)
    {
        $db = Database::getConnection();

        try {
            $db->beginTransaction();
            $stmt = $db->prepare(
                "SELECT pp.id_user, u.email, u.nama_user
                FROM pengajuan_rekruter pp
                JOIN user u ON u.id_user = pp.id_user
                WHERE pp.id_pengajuan = ? AND pp.status_pengajuan = 'menunggu' FOR UPDATE"
            );

            $stmt->execute([$id_pengajuan]);
            $calon = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$calon) {
                $db->rollBack();
                return false;
            }

            $id_user = $calon['id_user'];

            $stmt = $db->prepare("UPDATE pengajuan_rekruter SET status_pengajuan = ? WHERE id_pengajuan = ?");
            $stmt->execute([$keputusan, $id_pengajuan]);

            $db->commit();
            return $calon;
        } catch (\PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }
}