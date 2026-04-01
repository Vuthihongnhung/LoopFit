<?php
include_once("../../config/database.php");

$sql = "SELECT 
            o.id,
            u.name AS user_name,
            u.email,
            o.total_price,
            o.status,
            o.rent_date,
            o.return_date,
            o.actual_return_date,
            o.late_fee,
            o.created_at
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    "success" => true,
    "data" => $data
]);