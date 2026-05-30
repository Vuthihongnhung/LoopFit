<?php
require_once __DIR__ . '/../public/index.php'; 

use App\Models\Order;
use App\Services\EmailService;

$orderModel = new Order();
// Lấy các đơn đang thuê (status='renting') có ngày trả dự kiến là ngày mai
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$sql = "SELECT * FROM orders WHERE status = 'renting' AND return_date = ?";
$stmt = $orderModel->getConnection()->prepare($sql);
$stmt->bind_param('s', $tomorrow);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$emailService = new EmailService();
foreach ($orders as $order) {
    $emailService->sendReturnReminder($order);
    // Tránh gửi nhiều lần: có thể đánh dấu đã gửi trong bảng orders (thêm cột reminded_at)
}
echo "Đã gửi " . count($orders) . " email nhắc nhở.\n";