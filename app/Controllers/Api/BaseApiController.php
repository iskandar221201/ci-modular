<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseApiController extends BaseController
{
    protected ?object $apiUser = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        // Force Content-Type: application/json on all API responses.
        $this->response->setHeader('Content-Type', 'application/json');

        // Populate $apiUser from the Shield token authenticator if the request is authenticated.
        if (function_exists('auth')) {
            $authenticator = auth('tokens');
            if ($authenticator->loggedIn()) {
                $this->apiUser = $authenticator->user();
            }
        }

        // Content-Type: application/json validation for POST/PUT/PATCH requests
        // is handled by JsonBodyFilter (app/Filters/JsonBodyFilter.php),
        // so the CI4 response pipeline (after-filters, CORS headers) is never bypassed.
    }
}
