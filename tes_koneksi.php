<?php
require_once __DIR__ . '/autoload.php';

use pbo_FP\Core\Database;

$db = Database::getConnection();
echo "Konek ke database";