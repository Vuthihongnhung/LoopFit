<?php
namespace App\Middlewares;

class UserMiddleware
{
    /* ===== Kiểm tra người dùng đã đăng nhập (có session user) hay chưa ===== */
    public static function handle()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}