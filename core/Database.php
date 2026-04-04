<?php
namespace Core;

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'loopfit';
    private $conn;
    
    public function getConnection() {
        $this->conn = new \mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Kết nối database thất bại: " . $this->conn->connect_error);
        }
        return $this->conn;
    }
}