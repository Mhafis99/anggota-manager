<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../models/Anggota.php';

class AnggotaController {
    private $model;

    public function __construct() {
        $this->model = new Anggota();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
        $id = isset($request[1]) ? (int)$request[1] : null;

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getOne($id);
                } else {
                    $this->getAll();
                }
                break;
            case 'POST':
                $this->create();
                break;
            case 'PUT':
                $this->update($id);
                break;
            case 'DELETE':
                $this->delete($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    private function getAll() {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;

        $stmt = $this->model->read($search, $page, $limit);
        $total = $this->model->count($search);
        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        http_response_code(200);
        echo json_encode([
            "data" => $data,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $limit,
                "total" => (int)$total,
                "total_pages" => ceil($total / $limit)
            ]
        ]);
    }

    private function getOne($id) {
        $this->model->id = $id;
        if ($this->model->readOne()) {
            http_response_code(200);
            echo json_encode($this->model);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Anggota tidak ditemukan"]);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (!$this->validateInput($data)) {
            http_response_code(400);
            echo json_encode(["message" => "Data tidak lengkap atau email tidak valid"]);
            return;
        }

        $this->model->nama = $data->nama;
        $this->model->email = $data->email;
        $this->model->no_hp = $data->no_hp;
        $this->model->alamat = $data->alamat;

        if ($this->model->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Anggota berhasil ditambahkan"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal menambahkan anggota"]);
        }
    }

    private function update($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID diperlukan"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!$this->validateInput($data)) {
            http_response_code(400);
            echo json_encode(["message" => "Data tidak lengkap atau email tidak valid"]);
            return;
        }

        $this->model->id = $id;
        $this->model->nama = $data->nama;
        $this->model->email = $data->email;
        $this->model->no_hp = $data->no_hp;
        $this->model->alamat = $data->alamat;

        if ($this->model->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Anggota berhasil diperbarui"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal memperbarui anggota"]);
        }
    }

    private function delete($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID diperlukan"]);
            return;
        }

        $this->model->id = $id;
        if ($this->model->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Anggota berhasil dihapus"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal menghapus anggota"]);
        }
    }

    private function validateInput($data) {
        if (!isset($data->nama, $data->email, $data->no_hp, $data->alamat)) {
            return false;
        }
        if (empty(trim($data->nama)) || empty(trim($data->email)) || empty(trim($data->no_hp)) || empty(trim($data->alamat))) {
            return false;
        }
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        // Validasi nomor HP sederhana (hanya angka, min 10 digit)
        if (!preg_match('/^[0-9]{10,15}$/', $data->no_hp)) {
            return false;
        }
        return true;
    }
}
?>