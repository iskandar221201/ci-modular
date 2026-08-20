<?php

namespace App\Libraries\Storage;

use App\Contracts\StorageDriverInterface;

class LocalDriver implements StorageDriverInterface
{
    protected string $basePath;
    protected string $baseUrl;

    public function __construct(string $basePath, string $baseUrl)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    public function put(string $relativePath, string $content): bool
    {
        $fullPath = $this->resolvePath($relativePath);
        $directory = dirname($fullPath);

        if (! is_dir($directory) && ! @mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Failed to create destination directory: ' . $directory);
        }

        if (file_put_contents($fullPath, $content) === false) {
            throw new \RuntimeException('Failed to write file to local storage: ' . $fullPath);
        }

        return true;
    }

    public function delete(string $relativePath): bool
    {
        $fullPath = $this->resolvePath($relativePath);

        if (! file_exists($fullPath)) {
            return true;
        }

        if (! @unlink($fullPath)) {
            log_message('error', 'Failed to delete uploaded file: ' . $fullPath);
            return false;
        }

        return true;
    }

    public function url(string $relativePath): string
    {
        return $this->baseUrl . ltrim($relativePath, '/');
    }

    protected function resolvePath(string $relativePath): string
    {
        $normalizedRelativePath = ltrim($relativePath, '/\\');

        if (strpos($normalizedRelativePath, 'uploads/') === 0) {
            $normalizedRelativePath = substr($normalizedRelativePath, strlen('uploads/'));
        }

        return $this->basePath . str_replace('/', DIRECTORY_SEPARATOR, $normalizedRelativePath);
    }
}
