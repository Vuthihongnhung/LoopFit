<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    //$_SESSION['user_id'] = 1;
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

// ===== PRODUCT DETAIL DYNAMIC =====
    if (preg_match('#^product/([0-9]+)$#', $url, $matches)) {
        $ctrl = new App\Controllers\User\ProductController();
        $ctrl->show($matches[1]);
        exit;
    }

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

            // ===== USER AUTH =====
    case 'login':
        $ctrl = new App\Controllers\User\AuthController();
        if ($method === 'POST') {
            $ctrl->login();          // xử lý đăng nhập
        } else {
            $ctrl->loginForm();      // hiển thị form login
        }
        break;

    case 'register':
        $ctrl = new App\Controllers\User\AuthController();
        if ($method === 'POST') {
            $ctrl->register();       // xử lý đăng ký
        } else {
            $ctrl->registerForm();   // hiển thị form register
        }
        break;

    case 'logout':
        $ctrl = new App\Controllers\User\AuthController();
        $ctrl->logout();
        break;

        // Profile
    case 'profile':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->profile();
        break;

    // API update profile
    case 'api/user/update-profile':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->updateProfile();
        break;

    // API change password
    case 'api/user/change-password':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->changePassword();
        break;

    case 'api/addresses':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->getAddresses();
        break;
    case 'api/address/add':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->addAddress();
        break;
    case 'api/address/update':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->updateAddress();
        break;
    case 'api/address/delete':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->deleteAddress();
        break;
    case 'api/address/set-default':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->setDefaultAddress();
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

    case 'api/cart/count':
        $ctrl = new App\Controllers\User\CartController();
        $ctrl->count();
        break;
    
    case 'api/products':
        $ctrl = new App\Controllers\User\ProductController();
        $ctrl->filterApi();
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

    case 'checkout/apply-voucher':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->applyVoucher();
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
    case 'admin/promotions/delete':
        $ctrl = new App\Controllers\Admin\PromotionController();
        $ctrl->delete();
        break;

     // ===== CATEGORIES =====
    case 'admin/categories':
        $ctrl = new App\Controllers\Admin\CategoryController();
        $ctrl->index();
        break;
    case 'admin/api/categories':
        $ctrl = new App\Controllers\Admin\CategoryController();
        $ctrl->apiGetList();
        break;
    case 'admin/categories/store':
        $ctrl = new App\Controllers\Admin\CategoryController();
        $ctrl->store();
        break;
    case 'admin/categories/update':
        $ctrl = new App\Controllers\Admin\CategoryController();
        $ctrl->update();
        break;
    case 'admin/categories/delete':
        $ctrl = new App\Controllers\Admin\CategoryController();
        $ctrl->delete();
        break;

    // ===== 404 =====
    default:
        http_response_code(404);
        echo "404 Not Found - Route: '$url'";
        break;
}