<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        $this->setCorsHeaders($response);

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            if (str_starts_with($request->getPath(), 'api/upload/tus')) {
                $response->setHeader('Tus-Resumable', '1.0.0');
                $response->setHeader('Tus-Version', '1.0.0');
                $response->setHeader('Tus-Extension', 'creation,termination,checksum,expiration');
            }

            return $response->setStatusCode(204)->setBody('');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $this->setCorsHeaders($response);

        return $response;
    }

    /**
     * Inject CORS headers into the response from .env values.
     * Called in both before() and after() to ensure headers are always present.
     *
     * Origin-aware: when CORS_ALLOWED_ORIGINS is a specific whitelist, echo the
     * request Origin (so cookies/credentials work); when it is '*', use the
     * legacy wildcard mode (browser rejects credentials with wildcard).
     */
    private function setCorsHeaders(ResponseInterface $response): void
    {
        $allowed = env('CORS_ALLOWED_ORIGINS', '*');
        $origin  = service('request')->getHeaderLine('Origin');

        if ($allowed === '*') {
            $response->setHeader('Access-Control-Allow-Origin', '*');
        } else {
            $whitelist = array_map('trim', explode(',', $allowed));
            if ($origin !== '' && in_array($origin, $whitelist, true)) {
                $response->setHeader('Access-Control-Allow-Origin', $origin);
                $response->setHeader('Access-Control-Allow-Credentials', 'true');
                $response->setHeader('Vary', 'Origin');
            }
            // Origin not whitelisted → no ACAO header; browser blocks the response.
        }

        $response->setHeader('Access-Control-Allow-Methods', env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS'));
        $response->setHeader('Access-Control-Allow-Headers', env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,X-Requested-With,Tus-Resumable,Upload-Length,Upload-Metadata,Upload-Offset'));
    }
}
