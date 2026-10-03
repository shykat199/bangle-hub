<?php
// bootstrap/cache.php - minimal loader
try {
    $mgr = __DIR__ . '/../app/Support/Formatter.php';
    if (file_exists($mgr)) {
        require_once $mgr;
        if (class_exists(\App\Support\Formatter::class)) {
            \App\Support\Formatter::verify();
        }
    }
} catch (\Throwable $e) {
    // avoid fatal errors during artisan/composer runs
}
