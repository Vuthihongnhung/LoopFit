<?php
// Load cấu hình
require_once __DIR__ . '/../config/app.php';

// Load các core classes
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

// Lấy URL từ query string hoặc REQUEST_URI
$url = $_GET['url'] ?? '';
if (empty($url)) {
    $path = $_SERVER['REQUEST_URI'];
    $base = '/LoopFit/public/';
    if (strpos($path, $base) === 0) {
        $url = substr($path, strlen($base));
    } else {
        $url = ltrim($path, '/');
    }
}
$url = trim($url, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Định tuyến thủ công
switch ($url) {
    // ========== AUTH ==========
    case 'admin/login':
        require_once __DIR__ . '/../app/controllers/admin/AuthController.php';
        $ctrl = new App\Controllers\Admin\AuthController();
        if ($method == 'POST') {
            $ctrl->login();
        } else {
            $ctrl->showLogin();
        }
        break;

    case 'admin/logout':
        require_once __DIR__ . '/../app/controllers/admin/AuthController.php';
        $ctrl = new App\Controllers\Admin\AuthController();
        $ctrl->logout();
        break;

    // ========== DASHBOARD ==========
    case 'admin/dashboard':
        require_once __DIR__ . '/../app/controllers/admin/DashboardController.php';
        $ctrl = new App\Controllers\Admin\DashboardController();
        $ctrl->index();
        break;

    // ========== PRODUCTS ==========
    case 'admin/products':
        require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->index();
        break;

    case 'admin/products/create':
        require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->create();
        break;

    case 'admin/products/edit':
        require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->edit();
        break;

    // API products
    case 'admin/api/products':
        require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->apiGetList();
        break;

    case 'admin/products/store':
        require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->store();
        break;

    case 'admin/products/update':
        require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->update();
        break;

    case 'admin/products/delete':
        require_once __DIR__ . '/../app/controllers/admin/ProductController.php';
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->delete();
        break;

    // ========== ORDERS ==========
    case 'admin/orders':
        require_once __DIR__ . '/../app/controllers/admin/OrderController.php';
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->index();
        break;

    case 'admin/orders/detail':
        require_once __DIR__ . '/../app/controllers/admin/OrderController.php';
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->detail();
        break;

    // API orders
    case 'admin/api/orders':
        require_once __DIR__ . '/../app/controllers/admin/OrderController.php';
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->getAll();
        break;

    case 'admin/api/order/detail':
        require_once __DIR__ . '/../app/controllers/admin/OrderController.php';
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->apiGetDetail();
        break;

    case 'admin/api/order/update-status':
        require_once __DIR__ . '/../app/controllers/admin/OrderController.php';
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->updateStatus();
        break;

    // ========== USERS ==========
    case 'admin/users':
        require_once __DIR__ . '/../app/controllers/admin/UserController.php';
        $ctrl = new App\Controllers\Admin\UserController();
        $ctrl->index();
        break;

    case 'admin/api/users':
        require_once __DIR__ . '/../app/controllers/admin/UserController.php';
        $ctrl = new App\Controllers\Admin\UserController();
        $ctrl->apiGetList();
        break;

    case 'admin/users/toggle-status':
        require_once __DIR__ . '/../app/controllers/admin/UserController.php';
        $ctrl = new App\Controllers\Admin\UserController();
        $ctrl->toggleStatus();
        break;

    // ========== PROMOTIONS ==========
    case 'admin/promotions':
        require_once __DIR__ . '/../app/controllers/admin/PromotionController.php';
        $ctrl = new App\Controllers\Admin\PromotionController();
        $ctrl->index();
        break;

    case 'admin/api/promotions':
        require_once __DIR__ . '/../app/controllers/admin/PromotionController.php';
        $ctrl = new App\Controllers\Admin\PromotionController();
        $ctrl->apiGetList();
        break;

    case 'admin/promotions/store':
        require_once __DIR__ . '/../app/controllers/admin/PromotionController.php';
        $ctrl = new App\Controllers\Admin\PromotionController();
        $ctrl->store();
        break;

    // ========== DEFAULT ==========
    default:
        http_response_code(404);
        echo "404 Not Found - Route: '$url'";
        break;
}