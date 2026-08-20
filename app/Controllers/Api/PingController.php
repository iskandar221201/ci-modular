<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * PingController — Health check controller.
 *
 * Used to validate that the API stack is running correctly:
 * - index()  → public endpoint, no auth required (GET /api/ping)
 * - check()  → protected endpoint, requires apiKeyFilter (GET /api/protected)
 *
 * NOTE: 'protected' is a PHP visibility keyword and cannot be used as a
 * method name. The method is named check() and the route is mapped to
 * GET /api/protected in app/Config/Routes.php.
 *
 * @see app/Config/Routes.php
 */
class PingController extends BaseApiController
{
    /**
     * Public health check — no authentication required.
     *
     * GET /api/ping
     *
     * Response: {"status":true,"code":200,"message":"pong","data":null}
     */
    public function index(): ResponseInterface
    {
        return $this->success(null, 'pong');
    }

    /**
     * Authenticated health check — requires valid API token.
     *
     * GET /api/protected  (routed to this method via Routes.php)
     *
     * Response: {"status":true,"code":200,"message":"OK","data":{"authenticated":true}}
     */
    public function check(): ResponseInterface
    {
        return $this->success(['authenticated' => true], 'OK');
    }
}
