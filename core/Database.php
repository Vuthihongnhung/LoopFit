<?php

namespace Core;

use mysqli;

class Database
{
    private $conn;

    public function getConnection()
{
    // Nếu đã có kết nối rồi thì trả về luôn, không tạo mới nữa
    if ($this->conn instanceof mysqli && $this->conn->ping()) {
        return $this->conn;
    }

    $config = require __DIR__ . '/../config/database.php';

    $this->conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database'],
        (int)($config['port'] ?? 3306)
    );

    if ($this->conn->connect_error) {
        die("❌ DB ERROR: " . $this->conn->connect_error);
    }

    $this->conn->set_charset($config['charset'] ?? 'utf8mb4');
    
    return $this->conn;
}
}