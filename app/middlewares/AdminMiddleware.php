<?php
namespace App\middlewares;

class AdminMiddleware {
    public static function check() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /LoopFit/public/admin/login');
        exit(); // BẮT BUỘC phải có để ngăn Dashboard Controller chạy tiếp
    }
}
}