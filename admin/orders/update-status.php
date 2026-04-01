<?php
include_once("../../config/database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';

    // Validate status
    $allowed = ['pending', 'confirmed', 'renting', 'returned'];

    if (!in_array($status, $allowed)) {
        echo json_encode([
            'success' => false,
            'error' => 'Trạng thái không hợp lệ'
        ]);
        exit();
    }

    // Nếu trả đồ → tính phí trễ
    if ($status == 'returned') {

        $result = $conn->query("SELECT return_date FROM orders WHERE id=$id");
        $row = $result->fetch_assoc();

        $return_date = strtotime($row['return_date']);
        $today = time();

        $late_fee = 0;

        if ($today > $return_date) {
            $days_late = ceil(($today - $return_date) / (60 * 60 * 24));
            $late_fee = $days_late * 50000; // 50k/ngày
        }

        $sql = "UPDATE orders 
                SET status='$status',
                    actual_return_date=NOW(),
                    late_fee=$late_fee
                WHERE id=$id";

    } else {
        $sql = "UPDATE orders SET status='$status' WHERE id=$id";
    }

    if ($conn->query($sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $conn->error
        ]);
    }
}
?>