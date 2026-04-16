<?php
class Database {
    private $config;
    public $conn;

    public function __construct() {
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
                $dir = dirname($path);

                // 1. Pastikan direktori database ada
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        throw new Exception("Gagal membuat folder database: $dir. Periksa permission.");
                    }
                }

                // 2. Pastikan direktori bisa ditulis
                if (!is_writable($dir)) {
                    throw new Exception("Folder database tidak writable: $dir. Ubah permission folder.");
                }

                // 3. Buat koneksi
                $dsn = "sqlite:" . $path;
                $this->conn = new PDO($dsn);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("PRAGMA foreign_keys = ON;");
            } 
            else {
                throw new Exception("Tipe database tidak dikenali. Pilih 'mysql' atau 'sqlite' di config.php.");
            }

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            // Tampilkan error yang lebih jelas untuk debugging
            die("Koneksi database gagal: " . $e->getMessage());
        } catch(Exception $e) {
            die($e->getMessage());
        }
        return $this->conn;
    }
}
?>