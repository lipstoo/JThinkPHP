<?php

use JThink\Core\Router;

/** @var Router $router */

// 基础路由
$router->get('/', 'Home@index');
$router->get('/about', 'Home@about');

// 用户模块 - RESTful资源路由
$router->resource('/users', 'User');

// 带中间件的路由
$router->group('/admin', function($router) {
    $router->get('/dashboard', 'Admin@dashboard');
    $router->resource('/orders', 'Order');
})->middleware(['Auth', 'Admin']);

// API路由组
$router->group('/api/v1', function($router) {
    $router->get('/products', 'Api\Product@index');
    $router->post('/products', 'Api\Product@store');
    $router->get('/products/{id}', 'Api\Product@show');
    $router->put('/products/{id}', 'Api\Product@update');
    $router->delete('/products/{id}', 'Api\Product@destroy');
});

// 自定义中间件路由
$router->get('/profile', 'User@profile', ['Auth']);
$router->post('/login', 'Auth@login');
$router->post('/logout', 'Auth@logout', ['Auth']);
?>
