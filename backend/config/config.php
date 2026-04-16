<?php
// backend/config/config.php

return [
    // Pilih tipe database: 'mysql' atau 'sqlite'
    'DB_TYPE' => 'sqlite', // Ganti ke 'sqlite' jika ingin menggunakan SQLite

    // Konfigurasi MySQL (hanya digunakan jika DB_TYPE = 'mysql')
    'MYSQL' => [
        'host'     => 'localhost',
        'db_name'  => 'db_anggota',
        'username' => 'root',
        'password' => '',
    ],

    // Konfigurasi SQLite (hanya digunakan jika DB_TYPE = 'sqlite')
    'SQLITE' => [
        'path' => __DIR__ . '/../../database/database.sqlite',
    ]
];
$localConfigFile = __DIR__ . '/config.local.php';
if (file_exists($localConfigFile)) {
    $localConfig = require $localConfigFile;
    $config = array_merge($config, $localConfig);
}

return $config;
?>