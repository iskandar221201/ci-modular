<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * SSOConfig — configuration for the JWT-based SSO layer.
 *
 * Values are populated from .env using explicit env() calls
 * to avoid ambiguity with CI4 BaseConfig auto-mapping.
 *
 * Environment keys:
 *   SSO_ENABLED    — set to true to activate SSOFilter verification
 *   SSO_PUBLIC_KEY — RSA public key (PEM format); used by Resource Server to verify tokens
 *   SSO_PRIVATE_KEY — RSA private key (PEM format); used ONLY by SSO Server to sign tokens
 *   SSO_TOKEN_TTL  — token lifetime in seconds (default: 3600)
 *
 * @warning Never commit SSO_PRIVATE_KEY to version control.
 */
class SSOConfig extends BaseConfig
{
    public bool   $enabled    = false;
    public string $publicKey  = '';
    public string $privateKey = '';
    public int    $tokenTtl   = 3600;

    public function __construct()
    {
        parent::__construct();

        $this->enabled    = (bool) env('SSO_ENABLED', false);
        $this->publicKey  = (string) env('SSO_PUBLIC_KEY', '');
        $this->privateKey = (string) env('SSO_PRIVATE_KEY', '');
        $this->tokenTtl   = (int) env('SSO_TOKEN_TTL', 3600);
    }
}
