<?php

$router->post('/api/cart/add', 'App\Controllers\User\CartController@add');
$router->post('/api/cart/update', 'App\Controllers\User\CartController@update');
$router->post('/api/cart/remove', 'App\Controllers\User\CartController@remove');

$router->get('/api/products', 'App\Controllers\User\ProductController@filterApi');