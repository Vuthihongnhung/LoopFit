<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "error" => "Phương thức không hợp lệ"
    ]);
    exit();
}

$id = $_POST['id'] ?? 0;

if (!$id) {
    echo json_encode([
        "success" => false,
        "error" => "Thiếu user id"
    ]);
    exit();
}

// Lấy trạng thái hiện tại (prepared)
$stmt = $conn->prepare("SELECT status FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode([
        "success" => false,
        "error" => "User không tồn tại"
    ]);
    exit();
}

// Toggle
$new_status = ($user['status'] === 'active') ? 'banned' : 'active';

// Update
$update = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$update->bind_param("si", $new_status, $id);

if ($update->execute()) {
    echo json_encode([
        "success" => true,
        "new_status" => $new_status
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $update->error
    ]);
}

$stmt->close();
$update->close();
?>