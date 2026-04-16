<?php
// Cấu hình chung cho ứng dụng

// Đường dẫn gốc của dự án (thay đổi nếu cần)
define('BASE_URL', 'http://localhost:8000');

// Đường dẫn vật lý đến thư mục uploads
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Đường dẫn URL đến thư mục uploads
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Tên ứng dụng
define('APP_NAME', 'LoopFit');

// Môi trường: development hoặc production
define('ENVIRONMENT', 'development');

// Bật hiển thị lỗi khi ở môi trường development
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}