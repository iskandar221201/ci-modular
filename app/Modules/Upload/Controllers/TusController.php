<?php

declare(strict_types=1);

namespace App\Modules\Upload\Controllers;

use App\Libraries\TusUploader;
use App\Shared\Controllers\BaseApiController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TusController extends BaseApiController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        \App\Shared\Controllers\BaseController::initController($request, $response, $logger);

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
