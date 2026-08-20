<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Users\Contracts\UserClientInterface;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Forward to the Users module's service registration so module consumers
     * resolve the UserClient via the central Services layer.
     */
    public static function userClient(bool $getShared = true): UserClientInterface
    {
        return \App\Modules\Users\Config\Services::userClient($getShared);
    }


    public static function postClient(bool $getShared = true): \App\Modules\Posts\Contracts\PostClientInterface
    {
        return \App\Modules\Posts\Config\Services::postClient($getShared);
    }
}