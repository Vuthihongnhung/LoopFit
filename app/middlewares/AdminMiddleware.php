<?php

namespace App\Middlewares;

class AdminMiddleware
{
    public static function handle()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo '403 Forbidden';
            exit;
        }
    }
}