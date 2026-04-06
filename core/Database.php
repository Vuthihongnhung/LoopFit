<?php
namespace Core; // BẮT BUỘC có dòng này

use mysqli;

class Database {
    private $host = "localhost";
    private $db_name = "loopfit";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            die("Kết nối MySQLi thất bại: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8");
        return $this->conn;
    }
}