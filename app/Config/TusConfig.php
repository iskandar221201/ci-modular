<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * TusConfig — configuration for TUS chunked/resumable upload layer.
 *
 * Values are populated from .env using explicit env() calls.
 *
 * Environment keys:
 *   TUS_UPLOAD_DIR     — directory for temporary partial uploads (default: writable/uploads/tus)
 *   TUS_MAX_SIZE       — max upload size in bytes (default: 1073741824 = 1 GB)
 *   TUS_EXPIRY_HOURS   — hours after which incomplete uploads are eligible for cleanup (default: 24)
 */
class TusConfig extends BaseConfig
{
    public string $uploadDir   = '';
    public int    $maxSize     = 1073741824;
    public int    $expiryHours = 24;

    public function __construct()
    {
        parent::__construct();

        $this->uploadDir   = (string) env('TUS_UPLOAD_DIR', WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'tus');
        $this->maxSize     = (int) env('TUS_MAX_SIZE', 1073741824);
        $this->expiryHours = (int) env('TUS_EXPIRY_HOURS', 24);
    }
}
