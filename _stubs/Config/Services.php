<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Config;

use App\Modules\{{MODULES}}\Client\{{MODULE}}Client;
use App\Modules\{{MODULES}}\Contracts\{{MODULE}}ClientInterface;
use App\Modules\{{MODULES}}\Services\{{MODULE}}Service;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function {{MODULE_LOWER}}Client(bool $getShared = true): {{MODULE}}ClientInterface
    {
        if ($getShared) {
            return static::getSharedInstance('{{MODULE_LOWER}}Client');
        }

        return new {{MODULE}}Client(new {{MODULE}}Service());
    }
}