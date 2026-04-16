-- Buat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS db_anggota;
USE db_anggota;

-- Tabel anggota
CREATE TABLE IF NOT EXISTS anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_hp VARCHAR(15) NOT NULL,
    alamat TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data contoh (opsional)
INSERT INTO anggota (nama, email, no_hp, alamat) VALUES
('Budi Santoso', 'budi@email.com', '081234567890', 'Jl. Melati No. 12, Jakarta'),
('Siti Aminah', 'siti@email.com', '081298765432', 'Jl. Anggrek No. 5, Bandung');

-- SQLite schema (untuk referensi)
CREATE TABLE anggota (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    no_hp TEXT NOT NULL,
    alamat TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);