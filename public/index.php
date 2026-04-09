<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    $_SESSION['user_id'] = 1;
}

// ===== CONFIG =====
require_once __DIR__ . '/../config/app.php';

// ===== CORE =====
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

// ===== AUTOLOAD =====
spl_autoload_register(function ($class) {
    // 1. Chuyển \ thành /
    $class = str_replace('\\', '/', $class);

    // 2. Chuyển App/ thành app/
    $class = str_replace('App/', 'app/', $class);

    // 3. Ép các thư mục về chữ thường
    $class = str_replace('Middlewares/', 'middlewares/', $class);
    $class = str_replace('Models/', 'models/', $class);
    $class = str_replace('Controllers/', 'controllers/', $class);

    $path = __DIR__ . '/../' . $class . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

// ===== LẤY URL =====
$url = $_GET['url'] ?? '';

if (empty($url)) {
    $path = $_SERVER['REQUEST_URI'];

    // tự động detect base path
    $base = dirname($_SERVER['SCRIPT_NAME']) . '/';

    if (strpos($path, $base) === 0) {
        $url = substr($path, strlen($base));
    } else {
        $url = ltrim($path, '/');
    }
}

// bỏ query string
$url = strtok($url, '?');

// bỏ dấu /
$url = trim($url, '/');

// method
$method = $_SERVER['REQUEST_METHOD'];

// ===== ROUTING =====
switch ($url) {

    // ===== USER =====
    case '':
    case 'index':
    case 'home':
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

    // ===== CART PAGE =====
    case 'cart':
        $ctrl = new App\Controllers\User\CartController();
        $ctrl->index();
        break;

    // ===== CART API =====
    case 'api/cart/add':
        $ctrl = new App\Controllers\User\CartController();
        $ctrl->add();
        break;

    case 'api/cart/update':
        $ctrl = new App\Controllers\User\CartController();
        $ctrl->update();
        break;

    case 'api/cart/remove':
        $ctrl = new App\Controllers\User\CartController();
        $ctrl->remove();
        break;

    // ===== CHECKOUT =====
    case 'checkout':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->index();
        break;

    case 'checkout/process':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->process();
        break;

    // ===== USER ORDERS =====
    case 'orders':
        $ctrl = new App\Controllers\User\OrderController();
        $ctrl->index();
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

    // ===== 404 =====
    default:
        http_response_code(404);
        echo "404 Not Found - Route: '$url'";
        break;
}