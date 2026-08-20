<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * JsonBodyFilter — validates Content-Type for requests that carry a body.
 *
 * Rejects POST/PUT/PATCH requests that do not send
 * Content-Type: application/json, returning a 400 response.
 *
 * This filter replaces the Content-Type validation block that previously
 * lived in BaseApiController::initController() with an exit() call — so
 * the CI4 response pipeline (after-filters, CORS headers, etc.) still runs.
 *
 * Registration: app/Config/Filters.php → alias 'jsonBodyFilter'
 * Applied to:   api/*  (before only)
 */
class JsonBodyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $method = strtoupper($request->getMethod());

        if (! in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            return null;
        }

        // Skip TUS upload routes — they use application/offset+octet-stream
        if (str_starts_with($request->getPath(), 'api/upload/tus')) {
            return null;
        }

        $contentType = $request->getHeaderLine('Content-Type');

        if (! str_contains($contentType, 'application/json')) {
            return service('response')
                ->setStatusCode(400)
                ->setHeader('Content-Type', 'application/json')
                ->setBody(api_error('Request body must be application/json', 400));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action in after — this filter only applies before the controller.
    }
}
