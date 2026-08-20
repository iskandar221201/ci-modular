<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public (no auth required)
$routes->group('api', static function (RouteCollection $routes): void {
    $routes->get('ping', '\App\Modules\Ping\Controllers\PingController::index');
});

// Protected (apiKeyFilter) — health check
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->get('protected', '\App\Modules\Ping\Controllers\PingController::check');
});