<?php
session_start();
include_once("config/database.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'];
$cart = $data['cart'];
$rent_date = $data['rent_date'];
$return_date = $data['return_date'];

if (!$cart || !$user_id) {
    echo json_encode(["success"=>false,"error"=>"Thiếu dữ liệu"]);
    exit();
}

// Tính tổng
$total = 0;

foreach ($cart as $item) {
    $product_id = $item['product_id'];
    $qty = $item['quantity'];

    $p = $conn->query("SELECT price, stock FROM products WHERE id=$product_id")->fetch_assoc();

    if ($p['stock'] < $qty) {
        echo json_encode(["success"=>false,"error"=>"Hết hàng"]);
        exit();
    }

    $total += $p['price'] * $qty;
}

// Tạo order
$conn->query("INSERT INTO orders (user_id, total_price, status, rent_date, return_date) 
              VALUES ($user_id, $total, 'pending', '$rent_date', '$return_date')");

$order_id = $conn->insert_id;

// Thêm order_details + trừ stock
foreach ($cart as $item) {

    $product_id = $item['product_id'];
    $qty = $item['quantity'];

    $p = $conn->query("SELECT price FROM products WHERE id=$product_id")->fetch_assoc();

    $conn->query("INSERT INTO order_details (order_id, product_id, quantity, price) 
                  VALUES ($order_id, $product_id, $qty, {$p['price']})");

    $conn->query("UPDATE products SET stock = stock - $qty WHERE id=$product_id");
}

echo json_encode([
    "success" => true,
    "order_id" => $order_id
]);
?>