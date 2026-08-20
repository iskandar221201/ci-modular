<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class SpaController extends \App\Shared\Controllers\BaseController
{
    public function index(): ResponseInterface
    {
        $path = FCPATH . 'dist/index.html';

        if (! is_file($path)) {
            throw new PageNotFoundException('SPA build not found. Run: cd frontend && npm run build');
        }

        $html = file_get_contents($path);

        if ($html === false) {
            throw new PageNotFoundException('SPA build could not be read.');
        }

        return $this->response
            ->setBody($html)
            ->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
