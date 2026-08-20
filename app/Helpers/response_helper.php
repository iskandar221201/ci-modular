<?php

use CodeIgniter\HTTP\ResponseInterface;

if (! function_exists('setAuthCookie')) {
    /**
     * Set the httpOnly auth cookie carrying the raw Shield access token.
     *
     * Secure attribute is auto-detected when AUTH_COOKIE_SECURE_AUTO=true
     * (reads $request->isSecure(), which honors X-Forwarded-Proto via proxyIPs).
     */
    function setAuthCookie(ResponseInterface $response, string $rawToken): void
    {
        $name       = env('AUTH_COOKIE_NAME', 'ck_token');
        $sameSite   = env('AUTH_COOKIE_SAMESITE', 'Lax');
        $secureAuto = env('AUTH_COOKIE_SECURE_AUTO', 'true') === 'true';
        $isSecure   = $secureAuto && service('request')->isSecure();

        $response->setCookie(
            $name,
            $rawToken,
            (int) env('AUTH_COOKIE_TTL', YEAR), // expire (seconds from now)
            '',
            '/',
            '',
            $isSecure,
            true,
            $sameSite,
        );
    }
}

if (! function_exists('clearAuthCookie')) {
    /**
     * Expire the httpOnly auth cookie (used on logout).
     */
    function clearAuthCookie(ResponseInterface $response): void
    {
        $response->deleteCookie(env('AUTH_COOKIE_NAME', 'ck_token'), '', '/');
    }
}

if (! function_exists('api_success')) {
    /**
     * Build a standard JSON success string.
     *
     * Returns a JSON-encoded envelope with status=true. Use this only in
     * contexts where ResponseInterface is not available (Filters, exception
     * handlers). Controllers should use ApiResponseTrait instead.
     */
    function api_success(mixed $data = null, string $message = 'Success', int $code = 200): string
    {
        $payload = [
            'status'  => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return '{"status":true,"code":' . $code . ',"message":"' . addslashes($message) . '","data":null}';
        }

        return $json;
    }
}

if (! function_exists('api_error')) {
    /**
     * Build a standard JSON error string.
     *
     * Returns a JSON-encoded envelope with status=false. Use this only in
     * contexts where ResponseInterface is not available (Filters, exception
     * handlers). Controllers should use ApiResponseTrait instead.
     */
    function api_error(string $message, int $code = 400, mixed $errors = null): string
    {
        $payload = [
            'status'  => false,
            'code'    => $code,
            'message' => $message,
            'errors'  => $errors,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return '{"status":false,"code":' . $code . ',"message":"' . addslashes($message) . '","errors":null}';
        }

        return $json;
    }
}
