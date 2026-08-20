<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AppLogger;

class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        // Fallback: if no Bearer header, try the httpOnly cookie (Vue SPA mode).
        if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
            $cookieToken = $request->getCookie(env('AUTH_COOKIE_NAME', 'ck_token'));
            if (! empty($cookieToken)) {
                $request->setHeader('Authorization', 'Bearer ' . $cookieToken);
                $authHeader = 'Bearer ' . $cookieToken;
                AppLogger::info('api.token.cookie_fallback', ['ip' => $request->getIPAddress()]);
            }
        }

        if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
            AppLogger::warning('api.token.invalid', [
                'ip'     => $request->getIPAddress(),
                'reason' => 'Missing or invalid Authorization header format'
            ]);
            
            return service('response')
                ->setStatusCode(401)
                ->setHeader('Content-Type', 'application/json')
                ->setJSON(api_error('Unauthorized', 401));
        }

        // Token extraction if needed manually, though Shield handles it via loggedIn()
        // $token = substr($authHeader, 7);

        try {
            $authenticated = auth('tokens')->loggedIn();
        } catch (\Throwable $e) {
            $authenticated = false;
            AppLogger::warning('api.token.exception', [
                'ip'     => $request->getIPAddress(),
                'reason' => 'Shield loggedIn() threw: ' . $e->getMessage(),
            ]);
        }

        if (! $authenticated) {
            AppLogger::warning('api.token.invalid', [
                'ip'     => $request->getIPAddress(),
                'reason' => 'Token rejected by Shield'
            ]);

            return service('response')
                ->setStatusCode(401)
                ->setHeader('Content-Type', 'application/json')
                ->setJSON(api_error('Unauthorized', 401));
        }
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
