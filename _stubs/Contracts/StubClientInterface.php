<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Contracts;

interface {{MODULE}}ClientInterface
{
    /**
     * @return array<int|string, mixed>|null
     */
    public function findById(int $id): ?array;

    public function existsById(int $id): bool;
}