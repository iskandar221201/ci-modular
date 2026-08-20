<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Protected (apiKeyFilter)
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->get('users', 'Api\UserController::index');
    $routes->post('users', 'Api\UserController::create');
    $routes->get('users/(:num)', 'Api\UserController::show/$1');
    $routes->put('users/(:num)', 'Api\UserController::update/$1');
    $routes->delete('users/(:num)', 'Api\UserController::delete/$1');
});
