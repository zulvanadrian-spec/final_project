<?php
// fungsi autoload ini agar otomatis ngeload class ketika dipanggil
// tidak perlu require satu-persatu

spl_autoload_register(function ($class) {
    $prefix = 'pbo_FP\\';

    // buang "pbo_FP\" dari depan nama class
    if (strpos($class, $prefix) === 0) {
        $class = substr($class, strlen($prefix));
    }

    // Models\Mahasiswa -> Models/Mahasiswa.php
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}