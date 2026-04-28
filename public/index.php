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
    $class = str_replace('\\', '/', $class);
    $class = str_replace('App/', 'app/', $class);
    $class = str_replace('Middlewares/', 'middlewares/', $class);
    $class = str_replace('Models/', 'models/', $class);
    $class = str_replace('Controllers/', 'controllers/', $class);
    $path = __DIR__ . '/../' . $class . '.php';
    if (file_exists($path)) require_once $path;
});

// ===== LẤY URL =====
$url = $_GET['url'] ?? '';
if (empty($url)) {
    $path = $_SERVER['REQUEST_URI'];
    $base = dirname($_SERVER['SCRIPT_NAME']) . '/';
    if (strpos($path, $base) === 0) {
        $url = substr($path, strlen($base));
    } else {
        $url = ltrim($path, '/');
    }
}
$url = strtok($url, '?');
$url = trim($url, '/');
$method = $_SERVER['REQUEST_METHOD'];

// ===== DYNAMIC ROUTES (trước switch) =====
// Chi tiết sản phẩm: /product/123
if (preg_match('#^product/([0-9]+)$#', $url, $matches)) {
    $ctrl = new App\Controllers\User\ProductController();
    $ctrl->show($matches[1]);
    exit;
}
// Chi tiết đơn hàng: /order/123
if (preg_match('#^order/(\d+)$#', $url, $matches)) {
    $ctrl = new App\Controllers\User\OrderController();
    $ctrl->detail($matches[1]);
    exit;
}

// ===== ROUTING =====
switch ($url) {

    // ------------------- USER -------------------
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
    
    case 'most-rented':
        $ctrl = new App\Controllers\User\ProductController();
        $ctrl->mostRented();
        break;

    case 'api/search':
        $ctrl = new App\Controllers\User\ProductController();
        $ctrl->searchApi();
        break;

    // Static pages
    case 've-loopfit':
        $ctrl = new App\Controllers\User\PageController();
        $ctrl->about();
        break;
    case 'cau-hoi-thuong-gap':
        $ctrl = new App\Controllers\User\PageController();
        $ctrl->faq();
        break;
    case 'chinh-sach-doi-tra':
        $ctrl = new App\Controllers\User\PageController();
        $ctrl->returnPolicy();
        break;
    case 'van-chuyen-thue':
        $ctrl = new App\Controllers\User\PageController();
        $ctrl->shipping();
        break;

    // Auth
    case 'login':
        $ctrl = new App\Controllers\User\AuthController();
        if ($method === 'POST') $ctrl->login();
        else $ctrl->loginForm();
        break;
    case 'register':
        $ctrl = new App\Controllers\User\AuthController();
        if ($method === 'POST') $ctrl->register();
        else $ctrl->registerForm();
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

    // API Profile (UserController)
    case 'api/user/update-profile':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->updateProfile();
        break;
    case 'api/user/change-password':
        $ctrl = new App\Controllers\User\UserController();
        $ctrl->changePassword();
        break;

    // API Addresses (dùng UserController cũ - giữ lại)
    case 'api/addresses':
        $ctrl = new App\Controllers\User\CheckoutController(); // chuyển sang CheckoutController
        $ctrl->addresses(); // method trả về JSON đúng format
        break;
    case 'user/get-addresses':
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

    // API Reviews
    case 'api/reviews/add':
        $ctrl = new App\Controllers\User\ReviewController();
        $ctrl->add();
        break;

    // Cart
    case 'cart':
        $ctrl = new App\Controllers\User\CartController();
        $ctrl->index();
        break;

    // Cart API
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

    // Product filter API
    case 'api/products':
        $ctrl = new App\Controllers\User\ProductController();
        $ctrl->filterApi();
        break;

    // ===================== CHECKOUT & ORDERS (BỔ SUNG) =====================
    // Trang checkout
    case 'checkout':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->index();
        break;

    // API checkout: áp dụng mã giảm giá (có thể dùng prefix /api/ hoặc không, nhưng nên dùng /api/)
    case 'checkout/apply-voucher':
    case 'api/checkout/apply-voucher':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->applyVoucher();
        break;

    // API xử lý đặt hàng
    case 'checkout/process':
    case 'api/checkout/process':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->process();
        break;

    case 'api/confirm-payment':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->confirmPayment();
        break;

    case 'api/qr/generate':
        $ctrl = new App\Controllers\User\QrController();
        $ctrl->generate();
        break;

    // ===== THÊM MỚI: Thanh toán QR =====
    case 'user/payment/qr':
        $ctrl = new App\Controllers\User\CheckoutController();
        $ctrl->showQR();
        break;

    case 'user/payment/confirm':
        if ($method === 'POST') {
            $ctrl = new App\Controllers\User\CheckoutController();
            $ctrl->confirmPayment();
        } else {
            http_response_code(405);
            echo "Method not allowed";
        }
        break;

    // Lấy danh sách đơn hàng của user (API cho AJAX)
    case 'api/orders':
        $ctrl = new App\Controllers\User\OrderController();
        $ctrl->apiList();
        break;

    // API hủy đơn hàng
    case 'api/order/cancel':
        $ctrl = new App\Controllers\User\OrderController();
        $ctrl->apiCancel();
        break;
    
    case 'order/success':
        $ctrl = new App\Controllers\User\OrderController();
        $ctrl->success();
        break;

    // API yêu cầu hoàn trả
    case 'api/order/return':
        $ctrl = new App\Controllers\User\OrderController();
        $ctrl->apiReturn();
        break;

    // Trang danh sách đơn hàng (user)
    case 'orders':
        $ctrl = new App\Controllers\User\OrderController();
        $ctrl->index();
        break;

    // ------------------- ADMIN -------------------
    case 'admin/login':
        $ctrl = new App\Controllers\Admin\AuthController();
        if ($method === 'POST') $ctrl->login();
        else $ctrl->showLogin();
        break;
    case 'admin/logout':
        $ctrl = new App\Controllers\Admin\AuthController();
        $ctrl->logout();
        break;
    case 'admin/dashboard':
        $ctrl = new App\Controllers\Admin\DashboardController();
        $ctrl->index();
        break;

    // Admin Products
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

    // Admin Orders
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

    // Admin Users
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

    // Admin Promotions
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

    // Admin Categories
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

    // ------------------- 404 -------------------
    default:
        http_response_code(404);
        echo "404 Not Found - Route: '$url'";
        break;
}