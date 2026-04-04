<?php
namespace App\Middlewares;

class AdminMiddleware {
    public static function check() {
        session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /LoopFit/public/index.php?url=admin/login');
            exit();
        }
    }
}