<?php

$router->get('', 'HomeController@index');
$router->get('index', 'HomeController@index');

$router->get('products', 'ProductController@index');

$router->get('product/{id}', 'User\ProductController@show');

$router->get('cart', 'CartController@index');

$router->get('checkout', 'CheckoutController@index');
$router->post('checkout/process', 'CheckoutController@process');

$router->get('orders', 'OrderController@index');