<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'] ?? 0;

    if ($id <= 0) {
        echo json_encode([
            "success" => false,
            "error" => "ID không hợp lệ"
        ]);
        exit();
    }

    // Lấy ảnh để xóa
    $result = $conn->query("SELECT image FROM products WHERE id=$id");
    $row = $result->fetch_assoc();

    if ($row && $row['image']) {
        $file_path = "../../uploads/" . $row['image'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Xóa sản phẩm
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Xóa thành công"
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