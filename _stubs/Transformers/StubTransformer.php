<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Transformers;

use App\Shared\Transformers\BaseTransformer;

class {{MODULE}}Transformer extends BaseTransformer
{
    public function transform(array $item): array
    {
        return $item;
    }
}