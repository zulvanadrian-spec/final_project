<?php
require_once __DIR__ . '/../autoload.php';
use pbo_FP\Models\Mahasiswa;

// Ambil kata kunci dari form search (jika ada)
$keyword = $_GET['keyword'] ?? '';
$dataMahasiswa = Mahasiswa::cari($keyword);
?>

<!-- Form Search Bar HTML -->
<form action="" method="GET">
    <input type="text" name="keyword" placeholder="Cari nama atau NIM..." value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">Cari</button>
</form>

<!-- Tabel Hasil Pencarian -->
<table>
    <?php foreach ($dataMahasiswa as $mhs): ?>
        <tr>
            <td><?= $mhs['nim'] ?></td>
            <td><?= $mhs['nama_user'] ?></td>
            <td><?= $mhs['email'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<br><a href="kelola_akun.php">Kelola Akun</a>
<br><a href="../proses/logout.php">Logout</a>