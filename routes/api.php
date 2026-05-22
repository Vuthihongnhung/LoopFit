<?php
// routes/api.php - Dành cho AJAX / JSON

use App\Middlewares\AuthMiddleware;

// === API công khai (không cần login) ===
$router->get('/api/search', [App\Controllers\User\ProductController::class, 'searchApi']);
$router->get('/api/products', [App\Controllers\User\ProductController::class, 'filterApi']);
$router->get('/api/product/variants', [App\Controllers\User\ProductController::class, 'getVariants']);
$router->get('/api/product/options', [App\Controllers\User\ProductController::class, 'getOptions']);
$router->post('/api/qr/generate', [App\Controllers\User\QrController::class, 'generate']);

// === API kiểm tra tồn kho sản phẩm (công khai) ===
$router->get('/api/check-stock', [App\Controllers\User\ProductController::class, 'checkStock']);
$router->get('/api/check-stock/{id}', [App\Controllers\User\ProductController::class, 'checkStock']);

// === API yêu cầu đăng nhập ===
$router->group(['middleware' => [AuthMiddleware::class]], function ($router) {
    
    // Giỏ hàng
    $router->post('/api/cart/add', [App\Controllers\User\CartController::class, 'add']);
    $router->post('/api/cart/update', [App\Controllers\User\CartController::class, 'update']);
    $router->post('/api/cart/remove', [App\Controllers\User\CartController::class, 'remove']);
    $router->get('/api/cart/count', [App\Controllers\User\CartController::class, 'count']);
    $router->post('/api/cart/update-item', [App\Controllers\User\CartController::class, 'updateItem']);
    
    // Địa chỉ
    $router->get('/api/addresses', [App\Controllers\User\CheckoutController::class, 'addresses']);
    $router->get('/user/get-addresses', [App\Controllers\User\UserController::class, 'getAddresses']);
    $router->post('/api/address/add', [App\Controllers\User\UserController::class, 'addAddress']);
    $router->post('/api/address/update', [App\Controllers\User\UserController::class, 'updateAddress']);
    $router->post('/api/address/delete', [App\Controllers\User\UserController::class, 'deleteAddress']);
    $router->post('/api/address/set-default', [App\Controllers\User\UserController::class, 'setDefaultAddress']);
    
    // Hồ sơ người dùng (cập nhật)
    $router->post('/api/user/update-profile', [App\Controllers\User\UserController::class, 'updateProfile']);
    $router->post('/api/user/change-password', [App\Controllers\User\UserController::class, 'changePassword']);
    $router->get('/user/api/active-vouchers', [App\Controllers\User\UserController::class, 'getActiveVouchers']);
    
    // Đánh giá
    $router->post('/api/reviews/add', [App\Controllers\User\ReviewController::class, 'add']);
    $router->post('/review/submit', [App\Controllers\User\ReviewController::class, 'submit']);
    
    // Voucher / thanh toán
    $router->post('/api/checkout/apply-voucher', [App\Controllers\User\CheckoutController::class, 'applyVoucher']);
    $router->post('/api/confirm-payment', [App\Controllers\User\CheckoutController::class, 'confirmPayment']);
    
    // Đơn hàng (API)
    $router->get('/api/user/orders', [App\Controllers\User\OrderController::class, 'apiList']);
    $router->post('/api/user/orders/cancel', [App\Controllers\User\OrderController::class, 'apiCancel']);
    $router->post('/api/user/orders/return', [App\Controllers\User\OrderController::class, 'apiReturn']);
});

$router->post('/checkout/apply-voucher', [App\Controllers\User\CheckoutController::class, 'applyVoucher']);
$router->post('/checkout/process', [App\Controllers\User\CheckoutController::class, 'process']);