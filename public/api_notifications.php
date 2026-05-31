<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/Notification.php';

$userId = $_SESSION['user_id'] ?? 0;
if (!$userId) {
    echo json_encode(['notifications' => []]);
    exit;
}

$notif = new Notification();
$notifications = $notif->getByUser($userId, 'customer', 10, 0);
echo json_encode(['notifications' => $notifications]);