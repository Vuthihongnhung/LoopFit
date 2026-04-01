<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

$sql = "SELECT 
            p.id,
            p.name,
            p.price,
            p.description,
            p.image,
            p.stock,
            p.category_id,
            c.name as category_name,
            p.created_at
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
    exit();
}

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = [
        "id" => (int)$row["id"],
        "name" => $row["name"],
        "price" => (float)$row["price"],
        "description" => $row["description"],
        "image" => $row["image"] ? "/uploads/" . $row["image"] : null,
        "stock" => (int)$row["stock"],
        "category" => [
            "id" => (int)$row["category_id"],
            "name" => $row["category_name"]
        ],
        "created_at" => $row["created_at"]
    ];
}

echo json_encode([
    "success" => true,
    "data" => $products
]);
?>