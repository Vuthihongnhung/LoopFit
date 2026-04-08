<?php

$router->post('api/cart/add', 'CartController@add');
$router->post('api/cart/update', 'CartController@update');
$router->post('api/cart/remove', 'CartController@remove');