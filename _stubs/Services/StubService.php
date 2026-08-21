<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Services;

use App\Modules\{{MODULES}}\Models\{{MODULE}}Model;
use App\Shared\Services\BaseService;

class {{MODULE}}Service extends BaseService
{
    protected string $modelClass = {{MODULE}}Model::class;

    protected function rules(): array
    {
        return [
            // 'field' => 'required|max_length[255]',
        ];
    }

    protected function updateRules(): array
    {
        // Override kalau update rules beda dari create rules
        // Contoh: hapus 'required' biar partial update bisa jalan
        return parent::updateRules();
    }
}