<?php

// Route login admin (công khai, không cần middleware)
$router->get('/admin/login', [App\Controllers\Admin\AuthController::class, 'showLogin']);
$router->post('/admin/login', [App\Controllers\Admin\AuthController::class, 'login']);
$router->get('/admin/logout', [App\Controllers\Admin\AuthController::class, 'logout']);

// Nhóm route admin yêu cầu AdminMiddleware
$router->group(['middleware' => [App\Middlewares\AdminMiddleware::class]], function ($router) {
    
    // ==================== DASHBOARD ====================
    $router->get('/admin', [App\Controllers\Admin\DashboardController::class, 'index']);
    $router->get('/admin/dashboard', [App\Controllers\Admin\DashboardController::class, 'index']);
    
    // API thống kê
    $router->get('/admin/api/stats', [App\Controllers\Admin\DashboardController::class, 'apiGetStats']);
    $router->get('/admin/api/monthly-revenue', [App\Controllers\Admin\DashboardController::class, 'apiMonthlyRevenue']);
    $router->get('/admin/api/top-products', [App\Controllers\Admin\DashboardController::class, 'apiTopProducts']);
    $router->get('/admin/api/pending-transfers', [App\Controllers\Admin\DashboardController::class, 'apiGetPendingTransferOrders']);
    $router->get('/admin/api/pending-deposits', [App\Controllers\Admin\DashboardController::class, 'apiGetPendingDepositReturns']);
    
    // ==================== QUẢN LÝ SẢN PHẨM ====================
    $router->get('/admin/products', [App\Controllers\Admin\ProductController::class, 'index']);
    $router->get('/admin/products/create', [App\Controllers\Admin\ProductController::class, 'create']);
    $router->get('/admin/products/edit', [App\Controllers\Admin\ProductController::class, 'edit']);
    $router->post('/admin/products/store', [App\Controllers\Admin\ProductController::class, 'store']);
    $router->post('/admin/products/update', [App\Controllers\Admin\ProductController::class, 'update']);
    $router->post('/admin/products/delete', [App\Controllers\Admin\ProductController::class, 'delete']);
    $router->get('/admin/api/products', [App\Controllers\Admin\ProductController::class, 'apiGetList']);
    $router->get('/admin/api/product', [App\Controllers\Admin\ProductController::class, 'apiGetOne']);
    // ==================== QUẢN LÝ ĐƠN HÀNG ====================
    $router->get('/admin/orders', [App\Controllers\Admin\OrderController::class, 'index']);
    $router->get('/admin/orders/detail', [App\Controllers\Admin\OrderController::class, 'detail']);
    
    // API lấy danh sách / chi tiết
    $router->get('/admin/api/orders', [App\Controllers\Admin\OrderController::class, 'getAll']);
    $router->get('/admin/api/order/detail', [App\Controllers\Admin\OrderController::class, 'apiGetDetail']);
    $router->get('/admin/api/pending-payments', [App\Controllers\Admin\OrderController::class, 'getPendingPayments']);
    $router->get('/admin/api/pending-deposits', [App\Controllers\Admin\OrderController::class, 'getPendingDeposits']);
    
    // Cập nhật trạng thái đơn hàng
    $router->post('/admin/api/order/update-status', [App\Controllers\Admin\OrderController::class, 'updateStatus']);
    $router->post('/admin/orders/updateStatus', [App\Controllers\Admin\OrderController::class, 'updateStatus']);
    
    // Xử lý trả hàng chi tiết
    $router->post('/admin/orders/processReturn', [App\Controllers\Admin\OrderController::class, 'processReturn']);
    $router->post('/admin/orders/updateActualReturn', [App\Controllers\Admin\OrderController::class, 'updateActualReturn']);
    $router->post('/admin/orders/approvePayment', [App\Controllers\Admin\OrderController::class, 'approvePayment']);
    
    // Quản lý phí trễ
    $router->post('/admin/orders/updateLateFeeStatus', [App\Controllers\Admin\OrderController::class, 'updateLateFeeStatus']);
    $router->post('/admin/orders/markLateFeePaid', [App\Controllers\Admin\OrderController::class, 'markLateFeePaid']);
    
    // Xử lý hoàn cọc
    $router->post('/admin/orders/processDeposit', [App\Controllers\Admin\OrderController::class, 'processDeposit']);
    
    // Thao tác hàng loạt
    $router->post('/admin/orders/batchApprove', [App\Controllers\Admin\OrderController::class, 'batchApprove']);
    $router->post('/admin/orders/batchCancel', [App\Controllers\Admin\OrderController::class, 'batchCancel']);
    
    // ==================== QUẢN LÝ NGƯỜI DÙNG ====================
    $router->get('/admin/users', [App\Controllers\Admin\UserController::class, 'index']);
    $router->get('/admin/api/users', [App\Controllers\Admin\UserController::class, 'apiGetList']);
    $router->post('/admin/users/toggle-status', [App\Controllers\Admin\UserController::class, 'toggleStatus']);
    
    // ==================== QUẢN LÝ KHUYẾN MÃI ====================
    $router->get('/admin/promotions', [App\Controllers\Admin\PromotionController::class, 'index']);
    $router->get('/admin/api/promotions', [App\Controllers\Admin\PromotionController::class, 'apiGetList']);
    $router->post('/admin/promotions/store', [App\Controllers\Admin\PromotionController::class, 'store']);
    $router->post('/admin/promotions/update', [App\Controllers\Admin\PromotionController::class, 'update']);
    $router->post('/admin/promotions/delete', [App\Controllers\Admin\PromotionController::class, 'delete']);
    
    // ==================== QUẢN LÝ DANH MỤC ====================
    $router->get('/admin/categories', [App\Controllers\Admin\CategoryController::class, 'index']);
    $router->get('/admin/api/categories', [App\Controllers\Admin\CategoryController::class, 'apiGetCategories']);
    $router->get('/admin/api/category', [App\Controllers\Admin\CategoryController::class, 'apiGetCategory']);
    $router->post('/admin/categories/store', [App\Controllers\Admin\CategoryController::class, 'store']);
    $router->post('/admin/categories/update', [App\Controllers\Admin\CategoryController::class, 'update']);
    $router->post('/admin/categories/delete', [App\Controllers\Admin\CategoryController::class, 'delete']);
    
    // ==================== QUẢN LÝ ĐÁNH GIÁ ====================
    $router->get('/admin/reviews', [App\Controllers\Admin\ReviewController::class, 'index']);
    $router->get('/admin/api/reviews', [App\Controllers\Admin\ReviewController::class, 'apiGetList']);
    $router->post('/admin/reviews/delete', [App\Controllers\Admin\ReviewController::class, 'delete']);
    $router->post('/admin/reviews/toggle-status', [App\Controllers\Admin\ReviewController::class, 'toggleStatus']);
    
    // ==================== QUẢN LÝ THANH TOÁN ====================
    $router->get('/admin/payments', [App\Controllers\Admin\PaymentController::class, 'index']);
    $router->get('/admin/api/payments', [App\Controllers\Admin\PaymentController::class, 'apiGetList']);
    $router->post('/admin/payments/confirm', [App\Controllers\Admin\PaymentController::class, 'confirmPayment']);
});