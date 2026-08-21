<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Transformers\AuthTransformer;
use App\Shared\Exceptions\ValidationException;
use App\Shared\Controllers\BaseApiController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AuthController extends BaseApiController
{
    protected AuthService $authService;

    protected AuthTransformer $transformer;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->authService = new AuthService();
        $this->transformer = new AuthTransformer();
    }

    public function login(): ResponseInterface
    {
        try {
            $credentials = $this->request->getJSON(true) ?? [
                'email'    => $this->request->getVar('email'),
                'password' => $this->request->getVar('password'),
            ];

            $result = $this->authService->login($credentials);

            // Set httpOnly cookie for the Vue SPA (same-origin). The raw token is
            // also returned in the body for backward-compat with programmatic API
            // clients (Bearer header mode). SSO branch does NOT set a cookie.
            if ($result['set_cookie']) {
                setAuthCookie($this->response, $result['token']);
            }

            return $this->success($this->transformer->transformLogin($result), 'Login berhasil');
        } catch (ValidationException $e) {
            return $this->error('Validation Error', 422, $e->getErrors());
        } catch (\App\Shared\Exceptions\ServiceException $e) {
            $this->logError('auth.login.failed', ['email' => $credentials['email'] ?? null], $e);

            return $this->error($e->getMessage(), $e->getStatusCode());
        }
    }

    public function logout(): ResponseInterface
    {
        $token = $this->request->getCookie(env('AUTH_COOKIE_NAME', 'ck_token'));
        if (empty($token)) {
            $header = $this->request->getHeaderLine('Authorization');
            if (str_starts_with($header, 'Bearer ')) {
                $token = substr($header, 7);
            }
        }

        $this->authService->logout($this->apiUser, $token);

        if ($this->apiUser !== null) {
            $this->logInfo('auth.logout', ['user_id' => $this->apiUser->id]);
        }

        clearAuthCookie($this->response);

        return $this->success(null, 'Logout berhasil');
    }

    public function me(): ResponseInterface
    {
        if ($this->apiUser === null) {
            return $this->error('Unauthorized', 401);
        }

        return $this->success($this->transformer->transformMe($this->apiUser), 'OK');
    }
}