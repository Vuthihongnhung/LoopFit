<?php

// Cart API
$router->post('/api/cart/add', 'App\Controllers\User\CartController@add');
$router->post('/api/cart/update', 'App\Controllers\User\CartController@update');
$router->post('/api/cart/remove', 'App\Controllers\User\CartController@remove');
$router->get('/api/cart/count', 'App\Controllers\User\CartController@getCount');

// Product filter API
$router->get('/api/products', 'App\Controllers\User\ProductController@filterApi');

// Address API (dùng UserController)
$router->get('/api/addresses', 'App\Controllers\User\UserController@getAddresses');
$router->post('/api/address/add', 'App\Controllers\User\UserController@addAddress');
$router->post('/api/address/update', 'App\Controllers\User\UserController@updateAddress');
$router->post('/api/address/delete', 'App\Controllers\User\UserController@deleteAddress');
$router->post('/api/address/set-default', 'App\Controllers\User\UserController@setDefaultAddress');

// Order API
$router->get('/api/orders', 'App\Controllers\User\OrderController@apiList');
$router->post('/api/order/cancel', 'App\Controllers\User\OrderController@apiCancel');
$router->post('/api/order/return', 'App\Controllers\User\OrderController@apiReturn');

// Checkout voucher API
$router->post('/api/checkout/apply-voucher', 'App\Controllers\User\CheckoutController@applyVoucher');

// Lưu ý: Các route /checkout/process và /user/payment/confirm đã có trong web.php

$router->get('/api/search', 'ProductController@searchApi');