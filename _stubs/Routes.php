<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    $routes->get('{{MODULES_LOWER}}', '\App\Modules\{{MODULES}}\Controllers\{{MODULE}}Controller::index');
    $routes->get('{{MODULES_LOWER}}/(:segment)', '\App\Modules\{{MODULES}}\Controllers\{{MODULE}}Controller::show/$1');
    $routes->post('{{MODULES_LOWER}}', '\App\Modules\{{MODULES}}\Controllers\{{MODULE}}Controller::create');
    $routes->put('{{MODULES_LOWER}}/(:segment)', '\App\Modules\{{MODULES}}\Controllers\{{MODULE}}Controller::update/$1');
    $routes->delete('{{MODULES_LOWER}}/(:segment)', '\App\Modules\{{MODULES}}\Controllers\{{MODULE}}Controller::delete/$1');
});