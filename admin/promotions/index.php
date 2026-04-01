<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

$sql = "SELECT * FROM promotions ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
    exit();
}

$promotions = [];

while ($row = $result->fetch_assoc()) {

    // Xác định trạng thái
    $now = date("Y-m-d H:i:s");

    if ($now < $row['start_date']) {
        $status = "upcoming";
    } elseif ($now > $row['end_date']) {
        $status = "expired";
    } else {
        $status = "active";
    }

    $promotions[] = [
        "id" => (int)$row["id"],
        "code" => $row["code"],
        "discount_percent" => (int)$row["discount_percent"],
        "start_date" => $row["start_date"],
        "end_date" => $row["end_date"],
        "status" => $status
    ];
}

echo json_encode([
    "success" => true,
    "data" => $promotions
]);
?>