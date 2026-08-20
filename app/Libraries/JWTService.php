<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\SSOConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWTService — sign and verify JWT tokens using RS256.
 *
 * sign()   — used by SSO Server to issue tokens.
 * verify() — used by Resource Server (SSOFilter) to validate incoming tokens.
 *
 * Both methods throw \RuntimeException on failure so the caller
 * (SSOFilter) can catch and return a 401 response without leaking details.
 *
 * @warning The private key must never be committed to version control.
 *          Store it in .env as SSO_PRIVATE_KEY.
 */
class JWTService
{
    private const ALGORITHM = 'RS256';

    private SSOConfig $config;

    public function __construct(?SSOConfig $config = null)
    {
        $this->config = $config ?? config('SSOConfig');
    }

    /**
     * Sign a payload and return a JWT string.
     * ONLY used by SSO Server.
     *
     * @param array<string, mixed> $payload Must include a 'sub' key identifying the subject.
     *
     * @throws \RuntimeException if private key is not configured or signing fails.
     */
    public function sign(array $payload): string
    {
        if ($this->config->privateKey === '') {
            throw new \RuntimeException('SSO private key is not configured');
        }

        $now = time();

        $payload = array_merge([
            'iat' => $now,
            'exp' => $now + $this->config->tokenTtl,
        ], $payload);

        try {
            return JWT::encode($payload, $this->config->privateKey, self::ALGORITHM);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to sign JWT: ' . $e->getMessage());
        }
    }

    /**
     * Verify a JWT string and return the decoded payload as an object.
     * Used by Resource Server (SSOFilter).
     *
     * @throws \RuntimeException if public key is not configured, token is invalid,
     *                           expired, signature fails, or required claim is missing.
     */
    public function verify(string $token): object
    {
        if ($this->config->publicKey === '') {
            throw new \RuntimeException('SSO public key is not configured');
        }

        try {
            $decoded = JWT::decode($token, new Key($this->config->publicKey, self::ALGORITHM));
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $payload = (array) $decoded;

        if (empty($payload['sub'])) {
            throw new \RuntimeException('Token missing required claim: sub');
        }

        return (object) $payload;
    }
}
