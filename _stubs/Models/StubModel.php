<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Models;

use App\Shared\Models\BaseModel;

class {{MODULE}}Model extends BaseModel
{
    protected $table          = '{{MODULES_LOWER}}';
    protected $primaryKey     = 'id';
    protected $allowedFields  = [];
    protected array $searchableFields = [];
}