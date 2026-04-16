<?php
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
    }

    // READ all with optional search & pagination
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

    // Count total for pagination
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

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET nama=:nama, email=:email, no_hp=:no_hp, alamat=:alamat";
        $stmt = $this->conn->prepare($query);

        $this->nama = htmlspecialchars(strip_tags($this->nama));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->no_hp = htmlspecialchars(strip_tags($this->no_hp));
        $this->alamat = htmlspecialchars(strip_tags($this->alamat));

        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":no_hp", $this->no_hp);
        $stmt->bindParam(":alamat", $this->alamat);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // READ one
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
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

    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET nama=:nama, email=:email, no_hp=:no_hp, alamat=:alamat WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->nama = htmlspecialchars(strip_tags($this->nama));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->no_hp = htmlspecialchars(strip_tags($this->no_hp));
        $this->alamat = htmlspecialchars(strip_tags($this->alamat));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":no_hp", $this->no_hp);
        $stmt->bindParam(":alamat", $this->alamat);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>