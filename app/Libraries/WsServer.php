<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\WsConfig;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer as WsServerWrapper;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Http\Server as ReactHttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

class WsServer implements MessageComponentInterface
{
    private WsConfig $config;

    private \SplObjectStorage $clients;

    private array $channels = [];

    private array $clientChannels = [];

    private int $connectionCounter = 0;

    private array $messageTimestamps = [];

    private int $channelCount = 0;

    public function __construct(?WsConfig $config = null)
    {
        $this->config = $config ?? config('WsConfig');
        $this->clients = new \SplObjectStorage();
    }

    public function run(): void
    {
        $loop = Loop::get();

        $webSock = new SocketServer(
            $this->config->host . ':' . $this->config->wsPort,
            [],
            $loop
        );

        new IoServer(
            new HttpServer(
                new WsServerWrapper($this)
            ),
            $webSock,
            $loop
        );

        $httpServer = new ReactHttpServer(
            $loop,
            \Closure::fromCallable([$this, 'handleHttp'])
        );

        $httpSock = new SocketServer(
            $this->config->host . ':' . $this->config->httpPort,
            ['tls' => false],
            $loop
        );
        $httpServer->listen($httpSock);

        fwrite(STDOUT, sprintf(
            "[WsServer] WebSocket listening on ws://%s:%d\n",
            $this->config->host,
            $this->config->wsPort
        ));
        fwrite(STDOUT, sprintf(
            "[WsServer] HTTP publish listening on http://%s:%d\n",
            $this->config->host,
            $this->config->httpPort
        ));

        if ($this->config->host !== '127.0.0.1') {
            log_message('warning', '[WsServer] WS_HOST is not 127.0.0.1 — HTTP publish port is exposed to the network');
        }

        $loop->run();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        $this->connectionCounter++;
        $connectionId = (string) $this->connectionCounter;
        $conn->connectionId = $connectionId;
        $this->clientChannels[$connectionId] = [];

        $conn->send(json_encode([
            'type' => 'welcome',
            'connectionId' => $connectionId,
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        if ($this->isRateLimited($from)) {
            return;
        }

        if (strlen((string) $msg) > $this->config->maxPayload) {
            return;
        }

        $data = json_decode((string) $msg, true);

        if (!is_array($data) || !isset($data['type'])) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Invalid message format']));
            return;
        }

        match ($data['type']) {
            'subscribe' => $this->handleSubscribe($from, $data),
            'unsubscribe' => $this->handleUnsubscribe($from, $data),
            'ping' => $from->send(json_encode(['type' => 'pong'])),
            default => $from->send(json_encode(['type' => 'error', 'message' => 'Unknown message type'])),
        };
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $connectionId = $conn->connectionId ?? null;

        if ($connectionId !== null && isset($this->clientChannels[$connectionId])) {
            foreach (array_keys($this->clientChannels[$connectionId]) as $channel) {
                $this->cleanupChannel($channel, $conn);
            }

            unset($this->clientChannels[$connectionId]);
        }

        unset($this->messageTimestamps[$conn->resourceId]);

        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        log_message('error', '[WsServer] Connection error: ' . $e->getMessage());
        $conn->close();
    }

    private function handleSubscribe(ConnectionInterface $conn, array $data): void
    {
        $channel = $data['channel'] ?? '';

        if (!$this->isValidChannel($channel)) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Invalid channel name',
            ]));
            return;
        }

        $this->subscribeToChannel($conn, $channel);
    }

    private function handleUnsubscribe(ConnectionInterface $conn, array $data): void
    {
        $channel = $data['channel'] ?? '';

        $this->cleanupChannel($channel, $conn);
    }

    private function handleHttp(ServerRequestInterface $request): Response
    {
        if ($request->getMethod() !== 'POST' || (string) $request->getUri()->getPath() !== '/publish') {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'Not found']));
        }

        $contentLength = (int) $request->getHeaderLine('Content-Length');

        if ($contentLength > $this->config->maxPayload) {
            return new Response(413, ['Content-Type' => 'application/json'], json_encode(['error' => 'Payload too large']));
        }

        $secret = $request->getHeaderLine('X-WS-Secret');

        if ($secret !== $this->config->secret) {
            log_message('warning', '[WsServer] Publish rejected: invalid secret');
            return new Response(403, ['Content-Type' => 'application/json'], json_encode(['error' => 'Forbidden']));
        }

        $body = (string) $request->getBody();
        $data = json_decode($body, true);

        if (!is_array($data) || empty($data['channel'])) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode(['error' => 'Invalid payload']));
        }

        $channel = $data['channel'];
        $payload = $data['payload'] ?? [];

        if (!$this->isValidChannel($channel)) {
            log_message('warning', '[WsServer] Publish rejected: invalid channel "' . $channel . '"');
            return new Response(400, ['Content-Type' => 'application/json'], json_encode(['error' => 'Invalid channel name']));
        }

        $this->broadcast($channel, $payload);

        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['ok' => true]));
    }

    private function broadcast(string $channel, array $payload): void
    {
        if (!isset($this->channels[$channel])) {
            return;
        }

        $message = json_encode(array_merge(['channel' => $channel], $payload));

        foreach ($this->channels[$channel] as $conn) {
            $conn->send($message);
        }
    }

    private function isRateLimited(ConnectionInterface $conn): bool
    {
        $id  = $conn->resourceId;
        $now = microtime(true);

        if (isset($this->messageTimestamps[$id]) && ($now - $this->messageTimestamps[$id]) < (1 / $this->config->rateLimit)) {
            return true;
        }

        $this->messageTimestamps[$id] = $now;

        return false;
    }

    private function subscribeToChannel(ConnectionInterface $conn, string $channel): void
    {
        if (!isset($this->channels[$channel])) {
            if ($this->channelCount >= $this->config->maxChannels) {
                $conn->send(json_encode([
                    'type' => 'error',
                    'message' => 'Channel limit reached',
                ]));
                return;
            }

            $this->channels[$channel] = [];
            $this->channelCount++;
        }

        $connectionId = $conn->connectionId;
        $this->channels[$channel][$connectionId] = $conn;
        $this->clientChannels[$connectionId][$channel] = true;
    }

    private function cleanupChannel(string $channel, ConnectionInterface $conn): void
    {
        $connectionId = $conn->connectionId;

        if ($connectionId === null || !isset($this->channels[$channel][$connectionId])) {
            return;
        }

        unset($this->channels[$channel][$connectionId]);
        unset($this->clientChannels[$connectionId][$channel]);

        if ($this->channels[$channel] === []) {
            unset($this->channels[$channel]);
            $this->channelCount--;
        }
    }

    private function isValidChannel(string $channel): bool
    {
        if (strlen($channel) > 64) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9_.-]+$/', $channel) === 1;
    }
}
