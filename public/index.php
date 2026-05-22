<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== CONFIG =====
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// ===== CORE =====
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';

// ===== AUTOLOAD =====
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require_once $file;
        return;
    }
    // Fallback cũ
    $class = str_replace('\\', '/', $class);
    $class = str_replace('App/', 'app/', $class);
    $path = __DIR__ . '/../' . $class . '.php';
    if (file_exists($path)) require_once $path;
});

// ===== XỬ LÝ URL =====
// Ưu tiên từ $_GET['url'] (do .htaccess tạo)
$url = $_GET['url'] ?? '';
if (empty($url)) {
    // Fallback: lấy từ REQUEST_URI
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = rtrim(dirname($scriptName), '/');
    if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    $url = trim(parse_url($requestUri, PHP_URL_PATH), '/');
}

$method = $_SERVER['REQUEST_METHOD'];

// ===== KHỞI TẠO ROUTER =====
$router = new Core\Router();

// ===== NẠP CÁC FILE ROUTE =====
require_once __DIR__ . '/../routes/web.php';
require_once __DIR__ . '/../routes/api.php';
require_once __DIR__ . '/../routes/admin.php';

// ===== DISPATCH =====
try {
    $reflection = new ReflectionMethod($router, 'dispatch');
    if ($reflection->getNumberOfParameters() === 2) {
        $router->dispatch($method, $url);
    } else {
        $router->dispatch($url);
    }
} catch (Exception $e) {
    http_response_code(404);
    echo "404 Not Found - " . $e->getMessage();
}