<?php

namespace App\Controllers\Api;

use App\Libraries\JWTService;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseApiController
{
    public function login(): ResponseInterface
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation Error', 422, $this->validator->getErrors());
        }

        $credentials = [
            'email'    => $this->request->getVar('email'),
            'password' => $this->request->getVar('password'),
        ];

        // Cek kredensial via Shield user provider (tanpa menyentuh session)
        $users  = auth()->getProvider();
        $user   = $users->findByCredentials(['email' => $credentials['email']]);

        if ($user === null) {
            return $this->error('Kredensial tidak valid', 401);
        }

        // Validasi password menggunakan Shield Passwords library
        $passwordHandler = service('passwords');
        if (! $passwordHandler->verify($credentials['password'], $user->password_hash)) {
            return $this->error('Kredensial tidak valid', 401);
        }

        if (! $user->active) {
            return $this->error('Akun belum aktif', 403);
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
            return $this->success(['token' => $token], 'Login berhasil');
        }

        // Fallback: Generate Shield Access Token
        $token = $user->generateAccessToken('api-login');

        // Set httpOnly cookie for the Vue SPA (same-origin). The raw token is
        // also returned in the body for backward-compat with programmatic API
        // clients (Bearer header mode). SSO branch above does NOT set a cookie.
        setAuthCookie($this->response, $token->raw_token);

        return $this->success([
            'token'    => $token->raw_token,
            'id'       => $user->id,
            'email'    => $user->email,
            'username' => $user->username,
        ], 'Login berhasil');
    }

    public function logout(): ResponseInterface
    {
        $user = $this->apiUser;

        if ($user !== null) {
            // Prefer the cookie token; fall back to the Bearer header.
            $tokenString = service('request')->getCookie(env('AUTH_COOKIE_NAME', 'ck_token'));
            if (empty($tokenString)) {
                $header = service('request')->getHeaderLine('Authorization');
                if (str_starts_with($header, 'Bearer ')) {
                    $tokenString = substr($header, 7);
                }
            }

            if (! empty($tokenString)) {
                $user->revokeAccessToken($tokenString);
            }

            $this->logInfo('auth.logout', ['user_id' => $user->id]);
        }

        clearAuthCookie($this->response);

        return $this->success(null, 'Logout berhasil');
    }

    public function me(): ResponseInterface
    {
        $user = $this->apiUser;

        if ($user === null) {
            return $this->error('Unauthorized', 401);
        }

        return $this->success([
            'id'       => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
        ], 'OK');
    }
}
