<?php
namespace Core;

class Model {
    public $conn;

    public function __construct() {
        // Gọi Database có kèm Namespace
        $db = new \Core\Database(); 
        $this->conn = $db->getConnection();
    }
}