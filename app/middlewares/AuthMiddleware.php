<?php
namespace App\Middlewares;

class AuthMiddleware
{
    /* ===== Kiểm tra trạng thái đăng nhập của người dùng ===== */
    public static function handle()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user']['id'])) {
            $loginUrl = defined('BASE_URL') ? BASE_URL . '/login' : '/login';
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /* ===== Lấy thông tin người dùng hiện tại từ session (nếu đã đăng nhập) ===== */
    public static function getUser()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user'] ?? null;
    }
}