<?php

$router->get('', 'HomeController@index');
$router->get('index', 'HomeController@index');

$router->get('products', 'ProductController@index');

$router->get('product/{id}', 'User\ProductController@show');

$router->get('cart', 'CartController@index');

$router->get('checkout', 'CheckoutController@index');
$router->post('checkout/process', 'CheckoutController@process');

$router->get('orders', 'OrderController@index');


// User profile (AJAX)
$router->post('/user/update-profile', 'UserController@updateProfile');
$router->post('/user/change-password', 'UserController@changePassword');
$router->get('/user/get-addresses', 'UserController@getAddresses');
$router->get('/user/get-address/{id}', 'UserController@getAddress');
$router->post('/user/add-address', 'UserController@addAddress');
$router->post('/user/update-address', 'UserController@updateAddress');
$router->post('/user/delete-address', 'UserController@deleteAddress');
$router->post('/user/set-default-address', 'UserController@setDefaultAddress');