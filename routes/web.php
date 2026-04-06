<?php

$router->get('', 'HomeController@index');
$router->get('index', 'HomeController@index');

$router->get('products', 'ProductController@index');
$router->get('product', 'ProductController@show');