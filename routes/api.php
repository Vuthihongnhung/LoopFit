<?php

// Cart API
$router->post('/api/cart/add', 'CartController@add');
$router->post('/api/cart/update', 'CartController@update');
$router->post('/api/cart/remove', 'CartController@remove');
$router->get('/api/cart/count', 'CartController@getCount');

// Product/Search API
$router->get('/api/products', 'ProductController@filterApi');
$router->get('/api/search', 'SearchController@api');

// Voucher API
$router->get('/api/vouchers', 'ApiController@vouchers');
$router->post('/api/voucher/apply', 'CheckoutController@applyVoucher');

// Chart/Dashboard API
$router->get('/api/chart-data', 'ApiController@chartData');

// Checkout/Payment API
$router->post('/api/checkout/process', 'CheckoutController@processCheckout');
$router->post('/api/payment/confirm', 'PaymentController@confirmPayment');

// Address API
$router->get('/api/addresses', 'UserController@getAddresses');
$router->post('/api/address/add', 'UserController@addAddress');
$router->post('/api/address/update', 'UserController@updateAddress');
$router->post('/api/address/delete', 'UserController@deleteAddress');
$router->post('/api/address/set-default', 'UserController@setDefaultAddress');
