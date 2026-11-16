<?php
require_once __DIR__ . '/../autoload.php';

use pbo_FP\Models\Event;

session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$daftarEvent = Event::semua();
?>
<h2>Daftar Event</h2>

<?php foreach ($daftarEvent as $e) { ?>
    <div>
        <h3><?= htmlspecialchars($e->getNamaEvent()) ?></h3>
        <p><?= htmlspecialchars($e->getTanggalEvent()) ?> | <?= htmlspecialchars($e->getStatusEvent()) ?></p>
    </div>
<?php } ?>