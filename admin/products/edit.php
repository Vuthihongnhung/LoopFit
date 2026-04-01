<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 0;
    $description = $_POST['description'] ?? '';
    $stock = $_POST['stock'] ?? 0;

    if ($id <= 0 || $name == '' || $price <= 0) {
        echo json_encode([
            "success" => false,
            "error" => "Dữ liệu không hợp lệ"
        ]);
        exit();
    }

    // Lấy ảnh cũ
    $old = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
    $image_name = $old['image'];

    // Nếu có upload ảnh mới
    if (!empty($_FILES['image']['name'])) {

        $target_dir = "../../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Update
    $stmt = $conn->prepare("UPDATE products 
        SET name=?, price=?, description=?, image=?, stock=?, category_id=? 
        WHERE id=?");

    $stmt->bind_param("sdssiii", 
        $name, 
        $price, 
        $description, 
        $image_name, 
        $stock, 
        $category_id,
        $id
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Cập nhật thành công"
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