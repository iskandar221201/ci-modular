<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// SPA catch-all — MUST be the last route.
// Serves the Vue SPA (frontend/dist/index.html) for every
// non-API GET request. Vue Router resolves the actual view.
$routes->get('(.*)', 'SpaController::index');
