<?php
include_once("../../config/database.php");

$order_id = $_GET['id'] ?? 0;

// Lấy thông tin đơn
$order_sql = "SELECT o.*, u.name, u.email 
              FROM orders o
              JOIN users u ON o.user_id = u.id
              WHERE o.id = $order_id";

$order_result = $conn->query($order_sql);
$order = $order_result->fetch_assoc();

// Lấy sản phẩm trong đơn
$detail_sql = "SELECT od.*, p.name 
               FROM order_details od
               JOIN products p ON od.product_id = p.id
               WHERE od.order_id = $order_id";

$detail_result = $conn->query($detail_sql);

$items = [];
while ($row = $detail_result->fetch_assoc()) {
    $items[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    "success" => true,
    "order" => $order,
    "items" => $items
]);