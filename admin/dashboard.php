<?php
include_once("config/database.php");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tổng sản phẩm
$product_result = $conn->query("SELECT COUNT(*) as total FROM products");
$product_count = $product_result ? $product_result->fetch_assoc()['total'] : 0;

// Tổng đơn
$order_result = $conn->query("SELECT COUNT(*) as total FROM orders");
$order_count = $order_result ? $order_result->fetch_assoc()['total'] : 0;

// Tổng user
$user_result = $conn->query("SELECT COUNT(*) as total FROM users");
$user_count = $user_result ? $user_result->fetch_assoc()['total'] : 0;

// Doanh thu
$revenue_result = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status='returned'");
$revenue_row = $revenue_result ? $revenue_result->fetch_assoc() : ['total' => 0];
$revenue = $revenue_row['total'] ?? 0;

// JSON
header('Content-Type: application/json');

echo json_encode([
    'total_products' => (int)$product_count,
    'total_orders' => (int)$order_count,
    'total_users' => (int)$user_count,
    'revenue' => (float)$revenue,
    'message' => 'success'
]);
?>