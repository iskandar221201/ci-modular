<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public (no auth required)
$routes->group('api', static function ($routes) {
    $routes->post('auth/login', 'Api\AuthController::login');
});

// Protected (apiKeyFilter)
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->post('auth/logout', 'Api\AuthController::logout');
    $routes->get('auth/me', 'Api\AuthController::me');
});
