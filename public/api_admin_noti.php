<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/Notification.php';

use App\Models\Notification;

// Gán cứng user_id = 0 cho admin
$notif = new Notification();
$data = $notif->getByUser(0, 'admin', 10, 0);
echo json_encode(['notifications' => $data]);