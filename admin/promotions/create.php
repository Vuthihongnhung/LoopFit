<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discount = $_POST['discount_percent'] ?? 0;
    $start = $_POST['start_date'] ?? null;
    $end = $_POST['end_date'] ?? null;

    // VALIDATE
    if ($code == '' || $discount <= 0 || $discount > 100) {
        echo json_encode([
            "success" => false,
            "error" => "Dữ liệu không hợp lệ"
        ]);
        exit();
    }

    // CHECK TRÙNG CODE (prepared)
    $check = $conn->prepare("SELECT id FROM promotions WHERE code=?");
    $check->bind_param("s", $code);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "error" => "Mã giảm giá đã tồn tại"
        ]);
        exit();
    }

    // INSERT
    $stmt = $conn->prepare("INSERT INTO promotions 
        (code, discount_percent, start_date, end_date) 
        VALUES (?, ?, ?, ?)");

    $stmt->bind_param("siss", $code, $discount, $start, $end);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Tạo mã thành công",
            "id" => $stmt->insert_id
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }

    $stmt->close();
}
?>