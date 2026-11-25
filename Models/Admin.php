<?php

namespace pbo_FP\Models;
use pbo_FP\Core\Database;

class Admin extends User
{
    private $id_admin;

    public function __construct($id_user, $nama_user, $email, $password, $id_admin)
    {
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
                        WHEN pp.status_pengajuan = 'menunggu' THEN 'calon_rekruter'
                        ELSE '-' END AS role,
                    pp.status_pengajuan
            FROM user u
            LEFT JOIN admin a ON a.id_user = u.id_user
            LEFT JOIN rekruter p ON p.id_user = u.id_user
            LEFT JOIN mahasiswa m ON m.id_user = u.id_user
            LEFT JOIN pengajuan_rekruter pp ON pp.id_user = u.id_user
            ORDER BY u.id_user DESC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function pengajuanMenunggu()
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT pp.id_pengajuan, pp.tanggal_pengajuan, pp.berkas, u.nama_user, u.email
            FROM pengajuan_rekruter pp
            JOIN user u ON u.id_user = pp.id_user
            WHERE pp.status_pengajuan = 'menunggu'
            ORDER BY pp.tanggal_pengajuan ASC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function putuskanPengajuan($id_pengajuan, $keputusan, $catatan = null)
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

            // Simpan status keputusan beserta catatan dari admin
            $stmt = $db->prepare("UPDATE pengajuan_rekruter SET status_pengajuan = ?, catatan = ? WHERE id_pengajuan = ?");
            $stmt->execute([$keputusan, $catatan, $id_pengajuan]);

            // Jika disetujui, daftarkan user ke tabel rekruter agar bisa login
            if ($keputusan === 'disetujui') {
                $stmt = $db->prepare(
                    "INSERT INTO rekruter (id_user)
                     SELECT ? WHERE NOT EXISTS (SELECT 1 FROM rekruter WHERE id_user = ?)"
                );
                $stmt->execute([$id_user, $id_user]);
            }

            $db->commit();
            return $calon;
        } catch (\PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }
}