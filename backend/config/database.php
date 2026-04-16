<?php
// backend/config/Database.php

class Database {
    private $config;
    public $conn;

    public function __construct() {
        // Muat konfigurasi
        $this->config = require __DIR__ . '/config.php';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            if ($this->config['DB_TYPE'] === 'mysql') {
                $db = $this->config['MYSQL'];
                $dsn = "mysql:host=" . $db['host'] . ";dbname=" . $db['db_name'];
                $username = $db['username'];
                $password = $db['password'];
                $options = [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ];
                $this->conn = new PDO($dsn, $username, $password, $options);
            } 
            elseif ($this->config['DB_TYPE'] === 'sqlite') {
                $path = $this->config['SQLITE']['path'];
                $dsn = "sqlite:" . $path;
                $this->conn = new PDO($dsn);
                // Aktifkan foreign key
                $this->conn->exec("PRAGMA foreign_keys = ON;");
            } 
            else {
                throw new Exception("Tipe database tidak dikenali. Pilih 'mysql' atau 'sqlite'.");
            }

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection error: " . $e->getMessage());
        } catch(Exception $e) {
            die($e->getMessage());
        }
        return $this->conn;
    }
}
?>