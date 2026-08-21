<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Libraries\JWTService;
use App\Shared\Exceptions\ServiceException;
use App\Shared\Services\BaseService;

/**
 * AuthService — authentication logic extracted from AuthController.
 *
 * Handles Shield-based credential verification, SSO token signing, and token
 * revocation. HTTP-layer concerns (httpOnly cookie, response envelope) stay
 * in the controller.
 */
class AuthService extends BaseService
{
    protected string $modelClass = '';

    /**
     * Verify credentials and produce a login payload.
     *
     * @param array{email: string, password: string} $credentials
     *
     * @return array{token: string, id: int, email: string, username: string, set_cookie: bool}
     *
     * @throws \App\Shared\Exceptions\ValidationException on missing/invalid input.
     * @throws ServiceException on invalid credentials or inactive account.
     */
    public function login(array $credentials): array
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        $this->validate($credentials, $rules);

        $users = auth()->getProvider();
        $user  = $users->findByCredentials(['email' => $credentials['email']]);

        if ($user === null) {
            throw new ServiceException('Kredensial tidak valid', 401);
        }

        $passwordHandler = service('passwords');
        if (! $passwordHandler->verify($credentials['password'], $user->password_hash)) {
            throw new ServiceException('Kredensial tidak valid', 401);
        }

        if (! $user->active) {
            throw new ServiceException('Akun belum aktif', 403);
        }

        // Bersihkan session Shield yang mungkin tersisa dari request sebelumnya
        // agar tidak terjadi konflik session state
        session()->remove('logged_in');
        session()->remove('id');
        session()->remove('user');

        // Check if SSO is enabled and this acts as SSO Server (has private key)
        $ssoConfig = config('SSOConfig');
        if ($ssoConfig && $ssoConfig->enabled && !empty($ssoConfig->privateKey)) {
            $token = (new JWTService())->sign([
                'sub'      => (string) $user->id,
                'user_id'  => $user->id,
                'email'    => $user->email,
                'roles'    => $user->getGroups(),
            ]);

            return [
                'token'       => $token,
                'id'          => $user->id,
                'email'       => $user->email,
                'username'    => $user->username,
                'set_cookie'  => false,
            ];
        }

        // Fallback: Generate Shield Access Token
        $token = $user->generateAccessToken('api-login');

        return [
            'token'       => $token->raw_token,
            'id'          => $user->id,
            'email'       => $user->email,
            'username'    => $user->username,
            'set_cookie'  => true,
        ];
    }

    /**
     * Revoke the active access token.
     *
     * @param ?string $tokenString raw Shield token (cookie or Bearer, without prefix) — passed from Controller
     */
    public function logout(?object $user, ?string $tokenString = null): void
    {
        if ($user === null) {
            return;
        }

        // HTTP extraction moved to Controller; fallback kept for BC (old call without token)
        $tokenString ??= service('request')->getCookie(env('AUTH_COOKIE_NAME', 'ck_token')) ?: null;
        if (empty($tokenString)) {
            $header = service('request')->getHeaderLine('Authorization');
            if (str_starts_with($header, 'Bearer ')) {
                $tokenString = substr($header, 7);
            }
        }

        if (! empty($tokenString)) {
            try {
                $user->revokeAccessToken($tokenString);
            } catch (\Throwable $e) {
                log_message('warning', '[AuthService] revoke failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * @return array{id: int, username: string, email: string}
     */
    public function me(object $user): array
    {
        return [
            'id'       => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
        ];
    }
}
