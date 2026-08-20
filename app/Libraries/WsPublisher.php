<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\WsConfig;

class WsPublisher
{
    private WsConfig $config;

    public function __construct(?WsConfig $config = null)
    {
        $this->config = $config ?? config('WsConfig');
    }

    public function publish(string $channel, array $payload): void
    {
        if ($this->config->enabled === false) {
            return;
        }

        try {
            $json = json_encode([
                'channel' => $channel,
                'payload' => $payload,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($json === false) {
                throw new \RuntimeException('json_encode failed: ' . json_last_error_msg());
            }

            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => implode("\r\n", [
                        'Content-Type: application/json',
                        'X-WS-Secret: ' . $this->config->secret,
                        'Content-Length: ' . strlen($json),
                    ]),
                    'content' => $json,
                    'timeout' => $this->config->publishTimeout,
                ],
            ]);

            $url = sprintf('http://%s:%d/publish', $this->config->host, $this->config->httpPort);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                $error = error_get_last();
                throw new \RuntimeException($error['message'] ?? 'Failed to connect to WebSocket server');
            }
        } catch (\Throwable $e) {
            log_message('error', '[WsPublisher] Failed to publish to channel "' . $channel . '": ' . $e->getMessage());
        }
    }
}
