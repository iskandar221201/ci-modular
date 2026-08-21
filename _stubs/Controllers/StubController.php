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

    public function show($id): ResponseInterface
    {
        return $this->success(null, 'OK');
    }

    public function create(): ResponseInterface
    {
        return $this->success(null, 'Created', 201);
    }

    public function update($id): ResponseInterface
    {
        return $this->success(null, 'Updated');
    }

    public function delete($id): ResponseInterface
    {
        return $this->success(null, 'Deleted');
    }
}