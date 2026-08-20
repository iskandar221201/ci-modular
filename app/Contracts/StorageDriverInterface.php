<?php

namespace App\Contracts;

interface StorageDriverInterface
{
    /**
     * Store a file at the given relative path.
     * $content is the raw file content (file_get_contents result).
     * Returns true on success, throws \RuntimeException on failure.
     */
    public function put(string $relativePath, string $content): bool;

    /**
     * Delete a file at the given relative path.
     * Returns true if deleted or not found, false if deletion failed.
     */
    public function delete(string $relativePath): bool;

    /**
     * Return the public URL for a given relative path.
     */
    public function url(string $relativePath): string;
}
