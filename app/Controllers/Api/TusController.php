<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\TusUploader;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TusController extends BaseApiController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        \App\Controllers\BaseController::initController($request, $response, $logger);

        if (function_exists('auth')) {
            $authenticator = auth('tokens');
            if ($authenticator->loggedIn()) {
                $this->apiUser = $authenticator->user();
            }
        }
    }

    public function handle(...$params): ResponseInterface
    {
        $uploader = new TusUploader();

        return $uploader->handle($this->request);
    }
}
