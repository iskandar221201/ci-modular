<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public (no auth required)
$routes->group('api', static function (RouteCollection $routes): void {
    $routes->post('auth/login', '\App\Modules\Auth\Controllers\AuthController::login');
});

// Protected (apiKeyFilter)
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->post('auth/logout', '\App\Modules\Auth\Controllers\AuthController::logout');
    $routes->get('auth/me', '\App\Modules\Auth\Controllers\AuthController::me');
});