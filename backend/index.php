<?php
// Aktifkan error reporting untuk development (nonaktifkan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/routes/api.php';
?>