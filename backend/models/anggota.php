<?php
// backend/models/Anggota.php
require_once __DIR__ . '/../config/Database.php';

class Anggota {
    private $conn;
    private $table_name = "anggota";

    public $id;
    public $nama;
    public $email;
    public $no_hp;
    public $alamat;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        // Buat tabel jika belum ada (self-contained, berfungsi untuk SQLite dan MySQL)
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        // Cek tipe database dari konfigurasi
        $config = require __DIR__ . '/../config/config.php';
        if ($config['DB_TYPE'] === 'mysql') {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                no_hp VARCHAR(15) NOT NULL,
                alamat TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        } else {
            // SQLite
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                no_hp TEXT NOT NULL,
                alamat TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $this->conn->exec($query);
    }

    public function read($search = '', $page = 1, $limit = 5) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM " . $this->table_name;
        $params = [];

        if (!empty($search)) {
            $query .= " WHERE nama LIKE :search OR email LIKE :search OR no_hp LIKE :search";
            $params[':search'] = "%$search%";
        }

        $query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function count($search = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $params = [];

        if (!empty($search)) {
            $query .= " WHERE nama LIKE :search OR email LIKE :search OR no_hp LIKE :search";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (nama, email, no_hp, alamat) 
                  VALUES (:nama, :email, :no_hp, :alamat)";
        $stmt = $this->conn->prepare($query);

        $this->nama = htmlspecialchars(strip_tags($this->nama));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->no_hp = htmlspecialchars(strip_tags($this->no_hp));
        $this->alamat = htmlspecialchars(strip_tags($this->alamat));

        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":no_hp", $this->no_hp);
        $stmt->bindParam(":alamat", $this->alamat);

        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->nama = $row['nama'];
            $this->email = $row['email'];
            $this->no_hp = $row['no_hp'];
            $this->alamat = $row['alamat'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nama = :nama, email = :email, no_hp = :no_hp, alamat = :alamat 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->nama = htmlspecialchars(strip_tags($this->nama));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->no_hp = htmlspecialchars(strip_tags($this->no_hp));
        $this->alamat = htmlspecialchars(strip_tags($this->alamat));
        $this->id = (int)$this->id;

        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":no_hp", $this->no_hp);
        $stmt->bindParam(":alamat", $this->alamat);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = (int)$this->id;
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>