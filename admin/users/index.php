<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

$sql = "SELECT id, name, email, created_at 
        FROM users 
        ORDER BY id DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
    exit();
}

$users = [];

while ($row = $result->fetch_assoc()) {

    $users[] = [
        "id" => (int)$row["id"],
        "name" => $row["name"],
        "email" => $row["email"],
        "created_at" => $row["created_at"]
    ];
}

echo json_encode([
    "success" => true,
    "data" => $users
]);
?>