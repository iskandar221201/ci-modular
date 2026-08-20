<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Protected (apiKeyFilter)
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->get('users', '\App\Modules\Users\Controllers\UserController::index');
    $routes->post('users', '\App\Modules\Users\Controllers\UserController::create');
    $routes->get('users/(:num)', '\App\Modules\Users\Controllers\UserController::show/$1');
    $routes->put('users/(:num)', '\App\Modules\Users\Controllers\UserController::update/$1');
    $routes->delete('users/(:num)', '\App\Modules\Users\Controllers\UserController::delete/$1');
});