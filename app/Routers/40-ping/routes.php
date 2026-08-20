<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public (no auth required)
$routes->group('api', static function ($routes) {
    $routes->get('ping', 'Api\PingController::index');
});

// Protected (apiKeyFilter) — health check
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->get('protected', 'Api\PingController::check');
});
