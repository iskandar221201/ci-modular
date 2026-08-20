<?php

declare(strict_types=1);

namespace App\Modules\{{MODULES}}\Controllers;

use App\Shared\Controllers\BaseApiController;
use CodeIgniter\HTTP\ResponseInterface;

class {{MODULE}}Controller extends BaseApiController
{
    public function index(): ResponseInterface
    {
        return $this->success(null, 'OK');
    }
}