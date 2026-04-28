<?php
namespace App\middlewares;

class AdminMiddleware {
    public static function check() {
        if (!isset($_SESSION['admin_id'])) {
            // Kiểm tra nếu là AJAX request
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if ($isAjax) {
                // AJAX: trả về JSON lỗi (không ảnh hưởng trang khác)
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Session hết hạn, vui lòng đăng nhập lại'
                ]);
                exit();
            } else {
                // Request thường: redirect về login (giữ nguyên behavior cũ)
                header('Location: /LoopFit/public/admin/login');
                exit();
            }
        }
    }
}