<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\AppLogger;
use App\Libraries\JWTService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\SSOConfig;

/**
 * SSOFilter — verifies Bearer JWT tokens using the SSO layer.
 *
 * Behavior:
 *   - If SSO_ENABLED=false  → pass-through; no verification performed.
 *   - If token is missing or malformed → 401 Unauthorized.
 *   - If token is invalid/expired → 401 Unauthorized.
 *   - If token is valid → injects decoded payload into $request->ssoUser.
 *
 * This filter can be used alongside ApiKeyFilter on the same route group.
 *
 * Registration: app/Config/Filters.php → alias 'ssoFilter'
 */
class SSOFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $config = config('SSOConfig');

        // Pass-through when SSO is disabled — no change to request behavior.
        if ($config->enabled === false) {
            return null;
        }

        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
            AppLogger::warning('sso.token.missing', [
                'ip' => $request->getIPAddress(),
            ]);

            return service('response')
                ->setStatusCode(401)
                ->setHeader('Content-Type', 'application/json')
                ->setJSON(api_error('Unauthorized', 401));
        }

        $token = substr($authHeader, 7);

        try {
            $jwtService      = new JWTService($config);
            $payload         = $jwtService->verify($token);
            $request->ssoUser = $payload;
        } catch (\RuntimeException $e) {
            AppLogger::warning('sso.token.invalid', [
                'ip'     => $request->getIPAddress(),
                'reason' => $e->getMessage(),
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
        // No action in after — this filter only applies before the controller.
    }
}
