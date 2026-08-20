<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Services;

use App\Modules\{{MODULES}}\Models\{{MODULE}}Model;
use App\Shared\Services\BaseService;

class {{MODULE}}Service extends BaseService
{
    protected string $modelClass = {{MODULE}}Model::class;
}