<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===== LOAD CONFIG =====
require_once __DIR__ . '/../config/app.php';

// ===== LOAD CORE =====
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

// ===== AUTOLOAD (CHO APP) =====
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $class = str_replace('App/', 'app/', $class);

    $path = __DIR__ . '/../' . $class . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

// ===== LẤY URL CHUẨN =====
$url = $_GET['url'] ?? '';

if (empty($url)) {
    $path = $_SERVER['REQUEST_URI'];

    // tự động lấy base path (không hardcode)
    $base = dirname($_SERVER['SCRIPT_NAME']) . '/';

    if (strpos($path, $base) === 0) {
        $url = substr($path, strlen($base));
    } else {
        $url = ltrim($path, '/');
    }
}

// loại bỏ query string (?id=...)
$url = strtok($url, '?');

// loại bỏ dấu / đầu/cuối
$url = trim($url, '/');

// lấy method
$method = $_SERVER['REQUEST_METHOD'];


// ===== ROUTING =====
switch ($url) {

    // ===== USER =====
    case '':
    case 'index':
        $ctrl = new App\Controllers\User\HomeController();
        $ctrl->index();
        break;

    case 'products':
        $ctrl = new App\Controllers\User\ProductController();
        $ctrl->index();
        break;

    case 'product':
        $ctrl = new App\Controllers\User\ProductController();
        $ctrl->show();
        break;


    // ===== AUTH =====
    case 'admin/login':
        $ctrl = new App\Controllers\Admin\AuthController();
        if ($method === 'POST') {
            $ctrl->login();
        } else {
            $ctrl->showLogin();
        }
        break;

    case 'admin/logout':
        $ctrl = new App\Controllers\Admin\AuthController();
        $ctrl->logout();
        break;


    // ===== DASHBOARD =====
    case 'admin/dashboard':
        $ctrl = new App\Controllers\Admin\DashboardController();
        $ctrl->index();
        break;


    // ===== PRODUCTS =====
    case 'admin/products':
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->index();
        break;

    case 'admin/products/create':
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->create();
        break;

    case 'admin/products/edit':
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->edit();
        break;

    case 'admin/products/store':
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->store();
        break;

    case 'admin/products/update':
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->update();
        break;

    case 'admin/products/delete':
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->delete();
        break;

    case 'admin/api/products':
        $ctrl = new App\Controllers\Admin\ProductController();
        $ctrl->apiGetList();
        break;


    // ===== ORDERS =====
    case 'admin/orders':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->index();
        break;

    case 'admin/orders/detail':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->detail();
        break;

    case 'admin/api/orders':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->getAll();
        break;

    case 'admin/api/order/detail':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->apiGetDetail();
        break;

    case 'admin/api/order/update-status':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->updateStatus();
        break;

    case 'admin/orders/approvePayment':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->approvePayment();
        break;

    case 'admin/orders/updateStatusAfterReturn':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->updateStatusAfterReturn();
        break;

    case 'admin/orders/processDeposit':
        $ctrl = new App\Controllers\Admin\OrderController();
        $ctrl->processDeposit();
        break;
    // ===== USERS =====
    case 'admin/users':
        $ctrl = new App\Controllers\Admin\UserController();
        $ctrl->index();
        break;

    case 'admin/api/users':
        $ctrl = new App\Controllers\Admin\UserController();
        $ctrl->apiGetList();
        break;

    case 'admin/users/toggle-status':
        $ctrl = new App\Controllers\Admin\UserController();
        $ctrl->toggleStatus();
        break;


    // ===== PROMOTIONS =====
    case 'admin/promotions':
        $ctrl = new App\Controllers\Admin\PromotionController();
        $ctrl->index();
        break;

    case 'admin/api/promotions':
        $ctrl = new App\Controllers\Admin\PromotionController();
        $ctrl->apiGetList();
        break;

    case 'admin/promotions/store':
        $ctrl = new App\Controllers\Admin\PromotionController();
        $ctrl->store();
        break;
        // ===== REVIEWS =====
    case 'admin/reviews':
        $ctrl = new App\Controllers\Admin\ReviewController();
        $ctrl->index();
        break;

    case 'admin/api/reviews':
        $ctrl = new App\Controllers\Admin\ReviewController();
        $ctrl->apiGetList();
        break;

    case 'admin/reviews/delete':
        $ctrl = new App\Controllers\Admin\ReviewController();
        $ctrl->delete();
        break;

    case 'admin/reviews/toggle-status':
        $ctrl = new App\Controllers\Admin\ReviewController();
        $ctrl->toggleStatus();
        break;
        // ===== PAYMENTS =====
    case 'admin/payments':
        $ctrl = new App\Controllers\Admin\PaymentController();
        $ctrl->index();
        break;

    case 'admin/api/payments':
        $ctrl = new App\Controllers\Admin\PaymentController();
        $ctrl->apiGetList();
        break;

    case 'admin/payments/confirm':
        $ctrl = new App\Controllers\Admin\PaymentController();
        $ctrl->confirmPayment();
        break;
    // ===== DEFAULT =====
    default:
        http_response_code(404);
        echo "404 Not Found - Route: '$url'";
        break;
}