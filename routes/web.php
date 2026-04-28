<?php
// routes/web.php

// Trang chủ
$router->get('/', 'HomeController@index');
$router->get('/index', 'HomeController@index');

// Sản phẩm
$router->get('/products', 'ProductController@index');
$router->get('/product/{id}', 'User\ProductController@show');

// Giỏ hàng
$router->get('/cart', 'CartController@index');
// API giỏ hàng (có thể để riêng, nhưng tạm thời ở đây)
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');
$router->get('/cart/count', 'CartController@count');

// Thanh toán
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process')->middleware('AuthMiddleware');
$router->post('/checkout/apply-voucher', 'CheckoutController@applyVoucher');

// QR thanh toán
$router->get('/user/payment/qr', 'User\CheckoutController@showQR');
$router->post('/user/payment/confirm', 'User\CheckoutController@confirmPayment');

// Đơn hàng (User)
$router->get('/orders', 'OrderController@index');
$router->get('/order/{id}', 'OrderController@detail');          // dạng /order/123
$router->get('/order/success', 'OrderController@success');
// API đơn hàng (dùng cho AJAX)
$router->get('/api/orders', 'OrderController@apiList');
$router->post('/api/order/cancel', 'OrderController@apiCancel');
$router->post('/api/order/return', 'OrderController@apiReturn');

// Profile & Địa chỉ (có thể dùng prefix /api)
$router->post('/user/update-profile', 'UserController@updateProfile');
$router->post('/user/change-password', 'UserController@changePassword');
$router->get('/api/addresses', 'UserController@getAddresses');
$router->post('/api/address/add', 'UserController@addAddress');
$router->post('/api/address/update', 'UserController@updateAddress');
$router->post('/api/address/delete', 'UserController@deleteAddress');
$router->post('/api/address/set-default', 'UserController@setDefaultAddress');
$router->get('/user/get-addresses', 'UserController@getAddresses');

// Trang tĩnh
$router->get('/ve-loopfit', 'PageController@about');
$router->get('/cau-hoi-thuong-gap', 'PageController@faq');
$router->get('/chinh-sach-doi-tra', 'PageController@returnPolicy');
$router->get('/van-chuyen-thue', 'PageController@shipping');

// Sản phẩm nổi bật
$router->get('/most-rented', 'ProductController@mostRented');

// Auth (nếu chưa có)
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');