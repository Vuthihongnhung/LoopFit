<?php
// routes/web.php - Dành cho frontend (trả về HTML)

use App\Middlewares\UserMiddleware;

// Trang chủ
$router->get('/', [App\Controllers\User\HomeController::class, 'index']);
$router->get('/index', [App\Controllers\User\HomeController::class, 'index']);
$router->get('/home', [App\Controllers\User\HomeController::class, 'index']);

// Sản phẩm
$router->get('/products', [App\Controllers\User\ProductController::class, 'index']);
$router->get('/most-rented', [App\Controllers\User\ProductController::class, 'mostRented']);

// Chi tiết sản phẩm
$router->get('/product/{id}', [App\Controllers\User\ProductController::class, 'show']);

// API kiểm tra tồn kho (công khai)
$router->get('/check-stock', [App\Controllers\User\ProductController::class, 'checkStock']);

// Trang tĩnh
$router->get('/ve-loopfit', [App\Controllers\User\PageController::class, 'about']);
$router->get('/cau-hoi-thuong-gap', [App\Controllers\User\PageController::class, 'faq']);
$router->get('/chinh-sach-doi-tra', [App\Controllers\User\PageController::class, 'returnPolicy']);
$router->get('/van-chuyen-thue', [App\Controllers\User\PageController::class, 'shipping']);

// Auth (form + xử lý) - công khai
$router->get('/login', [App\Controllers\User\AuthController::class, 'loginForm']);
$router->post('/login', [App\Controllers\User\AuthController::class, 'login']);
$router->get('/register', [App\Controllers\User\AuthController::class, 'registerForm']);
$router->post('/register', [App\Controllers\User\AuthController::class, 'register']);
$router->get('/logout', [App\Controllers\User\AuthController::class, 'logout']);

// ===== NHÓM ROUTE CẦN ĐĂNG NHẬP VÀ CHỈ DÀNH CHO USER (KHÔNG CHO ADMIN) =====
$router->group(['middleware' => [UserMiddleware::class]], function($router) {
    // Profile
    $router->get('/profile', [App\Controllers\User\UserController::class, 'profile']);
    $router->post('/update-profile', [App\Controllers\User\UserController::class, 'updateProfile']);
    
    // Giỏ hàng
    $router->get('/cart', [App\Controllers\User\CartController::class, 'index']);
    
    // Thanh toán
    $router->get('/checkout', [App\Controllers\User\CheckoutController::class, 'index']);
    $router->post('/checkout/process', [App\Controllers\User\CheckoutController::class, 'process']);
    
    // QR thanh toán
    $router->get('/user/payment/qr', [App\Controllers\User\CheckoutController::class, 'showQR']);
    $router->post('/user/payment/confirm', [App\Controllers\User\CheckoutController::class, 'confirmPayment']);
    
    // Đơn hàng
    $router->get('/orders', [App\Controllers\User\OrderController::class, 'index']);
    $router->get('/order/success', [App\Controllers\User\OrderController::class, 'success']);
    $router->get('/order/{id}', [App\Controllers\User\OrderController::class, 'detail']);
    $router->get('/user/orders/detail', [App\Controllers\User\OrderController::class, 'detail']); 
    $router->post('/user/orders/payLateFee', [App\Controllers\User\OrderController::class, 'payLateFee']);
    
    // Đánh giá sản phẩm sau khi đơn hàng hoàn tất
    $router->get('/review/order/{order_id}/product/{product_id}', [App\Controllers\User\ReviewController::class, 'showForm']);
    $router->post('/review/submit', [App\Controllers\User\ReviewController::class, 'submit']);
});