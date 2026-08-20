<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Client;

use App\Modules\{{MODULES}}\Contracts\{{MODULE}}ClientInterface;
use App\Modules\{{MODULES}}\Services\{{MODULE}}Service;

class {{MODULE}}Client implements {{MODULE}}ClientInterface
{
    public function __construct(private {{MODULE}}Service $service) {}

    public function findById(int $id): ?array
    {
        $record = $this->service->findById($id);

        return $record === null ? null : (array) $record;
    }

    public function existsById(int $id): bool
    {
        return $this->service->findById($id) !== null;
    }
}