<?php

require_once "core/Database.php";

try {
    $db = new Database();
    $conn = $db->connect();
    echo "Kết nối database loopfit thành công!";
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}