<?php

declare(strict_types=1);

namespace App\Modules\Users\Client;

use App\Modules\Users\Contracts\UserClientInterface;
use App\Modules\Users\Services\UserService;

class UserClient implements UserClientInterface
{
    public function __construct(private UserService $service) {}

    public function findById(int $id): ?array
    {
        $user = $this->service->findById($id);

        if ($user === null) {
            return null;
        }

        return [
            'id'       => $user->id,
            'username' => $user->username,
            'email'    => $user->getEmail(),
        ];
    }

    public function existsById(int $id): bool
    {
        return $this->service->findById($id) !== null;
    }

    public function getBasicProfile(int $id): ?array
    {
        $user = $this->service->findById($id);

        if ($user === null) {
            return null;
        }

        return ['id' => $user->id, 'username' => $user->username];
    }
}