<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 0;
    $description = $_POST['description'] ?? '';
    $stock = $_POST['stock'] ?? 0;

    // VALIDATE
    if ($name == '' || $price <= 0 || $category_id <= 0) {
        echo json_encode([
            "success" => false,
            "error" => "Dữ liệu không hợp lệ"
        ]);
        exit();
    }

    // XỬ LÝ UPLOAD ẢNH
    $image_name = null;

    if (!empty($_FILES['image']['name'])) {

        $target_dir = "../../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // INSERT (Prepared Statement)
    $stmt = $conn->prepare("INSERT INTO products 
        (name, price, description, image, stock, category_id) 
        VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sdssii", 
        $name, 
        $price, 
        $description, 
        $image_name, 
        $stock, 
        $category_id
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Thêm sản phẩm thành công",
            "product_id" => $stmt->insert_id
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