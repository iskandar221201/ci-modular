<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * WsConfig — configuration for the WebSocket layer (Ratchet).
 *
 * Values are populated from .env using explicit env() calls
 * to avoid ambiguity with CI4 BaseConfig auto-mapping.
 *
 * Environment keys:
 *   WS_ENABLED    — set to true to activate WebSocket publishing
 *   WS_HOST       — bind address for both servers (default: 127.0.0.1)
 *   WS_PORT       — WebSocket client connections port (default: 8081)
 *   WS_HTTP_PORT  — internal HTTP publish port, CI4 → Ratchet (default: 8082)
 *   WS_SECRET     — shared secret for internal HTTP publish auth
 *   WS_MAX_PAYLOAD    — maximum payload size in bytes (default: 131072)
 *   WS_PUBLISH_TIMEOUT — HTTP publish timeout in seconds (default: 0.5)
 *   WS_RATE_LIMIT      — max messages per second per WebSocket connection (default: 20)
 *   WS_MAX_CHANNELS    — max unique channel names before new subscriptions are rejected (default: 10000)
 *
 * @warning WS_SECRET must match between CI4 .env and the running Ratchet process.
 */
class WsConfig extends BaseConfig
{
    public bool   $enabled    = false;
    public string $host       = '127.0.0.1';
    public int    $wsPort     = 8081;
    public int    $httpPort   = 8082;
    public string $secret     = '';
    public int    $maxPayload     = 131072;
    public float  $publishTimeout = 0.5;
    public int    $rateLimit      = 20;
    public int    $maxChannels    = 10000;

    public function __construct()
    {
        parent::__construct();

        $this->enabled        = (bool) env('WS_ENABLED', false);
        $this->host           = (string) env('WS_HOST', '127.0.0.1');
        $this->wsPort         = (int) env('WS_PORT', 8081);
        $this->httpPort       = (int) env('WS_HTTP_PORT', 8082);
        $this->secret         = (string) env('WS_SECRET', '');
        $this->maxPayload     = (int) env('WS_MAX_PAYLOAD', 131072);
        $this->publishTimeout = (float) env('WS_PUBLISH_TIMEOUT', 0.5);
        $this->rateLimit      = (int) env('WS_RATE_LIMIT', 20);
        $this->maxChannels    = (int) env('WS_MAX_CHANNELS', 10000);
    }
}
