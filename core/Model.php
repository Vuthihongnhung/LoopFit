<?php
namespace Core;

class Model {
    protected $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    protected function getConnection() {
        return $this->conn;
    }
}