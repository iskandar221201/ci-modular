<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Protected (apiKeyFilter) — TUS chunked upload
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->match(['options', 'post'], 'upload/tus', '\App\Modules\Upload\Controllers\TusController::handle');
    $routes->match(['options', 'post', 'patch', 'head', 'delete'], 'upload/tus/(:any)', '\App\Modules\Upload\Controllers\TusController::handle/$1');
});