<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Contracts\StorageDriverInterface;
use App\Libraries\Storage\LocalDriver;
use Config\TusConfig;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use TusPhp\Tus\Server;
use TusPhp\Cache\FileStore;

class TusUploader implements StorageDriverInterface
{
    protected StorageDriverInterface $storage;

    protected TusConfig $config;

    protected ?Server $server = null;

    public function __construct(?StorageDriverInterface $storage = null, ?TusConfig $config = null)
    {
        $this->config = $config ?? config('TusConfig');

        $baseUrl = base_url('uploads/');
        $this->storage = $storage ?? new LocalDriver(
            $this->config->uploadDir . DIRECTORY_SEPARATOR . 'final' . DIRECTORY_SEPARATOR,
            $baseUrl,
        );
    }

    public function put(string $relativePath, string $content): bool
    {
        return $this->storage->put($relativePath, $content);
    }

    public function delete(string $relativePath): bool
    {
        return $this->storage->delete($relativePath);
    }

    public function url(string $relativePath): string
    {
        return $this->storage->url($relativePath);
    }

    public function getStorage(): StorageDriverInterface
    {
        return $this->storage;
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $server = $this->getServer();

        $symfonyResponse = $server->serve();

        $ci4Response = service('response');
        $ci4Response->setStatusCode($symfonyResponse->getStatusCode());

        foreach ($symfonyResponse->headers->all() as $name => $values) {
            $ci4Response->setHeader($name, $values[0]);
        }

        $content = $symfonyResponse->getContent();
        if ($content !== false) {
            $ci4Response->setBody($content);
        }

        return $ci4Response;
    }

    public function cleanup(): array
    {
        $server = $this->getServer();
        $deleted = $server->handleExpiration();

        return [
            'deleted' => count($deleted),
            'errors'  => 0,
        ];
    }

    protected function getServer(): Server
    {
        if ($this->server === null) {
            $cacheDir = $this->config->uploadDir . DIRECTORY_SEPARATOR . '.cache';
            $cache    = new FileStore($cacheDir);
            $cache->setTtl($this->config->expiryHours * 3600);

            $this->server = new Server($cache);
            $this->server->setUploadDir($this->config->uploadDir);
            $this->server->setApiPath('/api/upload/tus');
            $this->server->setMaxUploadSize($this->config->maxSize);

            $this->server->middleware()->skip(\TusPhp\Middleware\Cors::class);

            $this->server->event()->addListener(
                \TusPhp\Events\UploadComplete::class,
                function (\TusPhp\Events\TusEvent $event): void {
                    $details = $event->getFile()->details();
                    log_message('info', '[TusUploader] Upload completed: ' . ($details['name'] ?? 'unknown'));
                },
            );
        }

        return $this->server;
    }
}
