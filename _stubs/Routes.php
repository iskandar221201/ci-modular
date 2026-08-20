<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->get('{{MODULES_LOWER}}', '\App\Modules\{{MODULES}}\Controllers\{{MODULE}}Controller::index');
});