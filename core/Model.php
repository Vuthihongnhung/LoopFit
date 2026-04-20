<?php
namespace Core;

class Model
{
    protected $conn;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';
        
        $this->conn = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            (int)($config['port'] ?? 3306)
        );

        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset($config['charset'] ?? 'utf8mb4');
    }
}