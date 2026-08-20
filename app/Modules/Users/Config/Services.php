<?php

declare(strict_types=1);

namespace App\Modules\Users\Config;

use App\Modules\Users\Client\UserClient;
use App\Modules\Users\Contracts\UserClientInterface;
use App\Modules\Users\Services\UserService;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function userClient(bool $getShared = true): UserClientInterface
    {
        if ($getShared) {
            return static::getSharedInstance('userClient');
        }

        return new UserClient(new UserService());
    }
}