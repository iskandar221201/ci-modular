<?php

declare(strict_types=1);

namespace App\Modules\Users\Contracts;

interface UserClientInterface
{
    /**
     * @return array{id: int, username: string, email: string}|null
     */
    public function findById(int $id): ?array;

    public function existsById(int $id): bool;

    /**
     * @return array{id: int, username: string}|null
     */
    public function getBasicProfile(int $id): ?array;
}