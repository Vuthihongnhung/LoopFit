<?php
namespace Core;

class Controller {
    protected function render($view, $data = []) {
        // Giải nén mảng thành các biến để dùng ở View
        extract($data);

        // __DIR__ là thư mục app/core, nên ta lùi 1 cấp rồi vào views
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "Lỗi: Không tìm thấy file view tại: " . $viewPath;
        }
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}