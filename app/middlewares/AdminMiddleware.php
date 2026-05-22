<?php
// app/middlewares/AdminMiddleware.php

namespace App\Middlewares;

class AdminMiddleware
{
    /* ===== Kiểm tra admin đã đăng nhập và có role là admin ===== */
    public static function handle()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin']['id']) || $_SESSION['admin']['role'] !== 'admin') {
            // chuyển hướng về admin/login
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }

    public static function check()
    {
        self::handle();
    }

    /* ===== Xử lý khi chưa đăng nhập admin hoặc không có quyền ===== */
    private static function unauthorized($message = 'Unauthorized')
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => $message,
                'redirect' => '/LoopFit/public/admin/login'
            ]);
            exit;
        } else {
            $loginUrl = defined('BASE_URL') ? BASE_URL . '/admin/login' : '/LoopFit/public/admin/login';
            header('Location: ' . $loginUrl);
            exit;
        }
    }
}