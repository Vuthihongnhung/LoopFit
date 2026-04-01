<?php

require_once __DIR__ . '/../app/controllers/ProductController.php';

$controller = new ProductController();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// /LoopFit/api.php/products
if ($path === '/LoopFit/api.php/products') {
    $controller->index();
}
// /LoopFit/api.php/products/1
elseif (preg_match('#^/LoopFit/api\.php/products/(\d+)$#', $path, $matches)) {
    $controller->show($matches[1]);
}
else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Route not found'
    ], JSON_UNESCAPED_UNICODE);
}