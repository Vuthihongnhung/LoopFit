<?php

$router->post('/api/cart/add', 'App\Controllers\User\CartController@add');
$router->post('/api/cart/update', 'App\Controllers\User\CartController@update');
$router->post('/api/cart/remove', 'App\Controllers\User\CartController@remove');

$router->get('/api/products', 'App\Controllers\User\ProductController@filterApi');
$router->get('/api/cart/count', 'App\Controllers\User\CartController@getCount');


// routes/api.php
$router->get('/api/addresses', 'UserController@getAddresses');
$router->post('/api/address/add', 'UserController@addAddress');
$router->post('/api/address/update', 'UserController@updateAddress');
$router->post('/api/address/delete', 'UserController@deleteAddress');
$router->post('/api/address/set-default', 'UserController@setDefaultAddress');
// Admin - Quản lý đơn hàng API
$router->get('/admin/api/orders', 'App\Controllers\Admin\OrderController@apiOrders');
$router->post('/admin/orders/approvePayment', 'App\Controllers\Admin\OrderController@approvePayment');
$router->get('/admin/orders/detail', 'App\Controllers\Admin\OrderController@detail');