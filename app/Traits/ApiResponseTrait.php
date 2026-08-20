<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

trait ApiResponseTrait
{
    /**
     * Return a success response with the standard envelope.
     *
     * Envelope: { "status": true, "code": $code, "message": $message, "data": $data }
     *
     * @warning Caller is responsible for filtering sensitive fields from $data
     *          before passing it to this method. This trait does NOT filter.
     */
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): ResponseInterface
    {
        $body = [
            'status'  => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];

        return $this->response->setStatusCode($code)->setJSON($body);
    }

    /**
     * Return an error response with the standard envelope.
     *
     * Envelope: { "status": false, "code": $code, "message": $message, "errors": $errors }
     *
     * @warning Caller is responsible for filtering sensitive fields from $errors
     *          before passing it to this method. This trait does NOT filter.
     */
    protected function error(string $message = 'Error', int $code = 400, mixed $errors = null): ResponseInterface
    {
        $body = [
            'status'  => false,
            'code'    => $code,
            'message' => $message,
            'errors'  => $errors,
        ];

        return $this->response->setStatusCode($code)->setJSON($body);
    }

    /**
     * Return a 201 Created response. Shorthand for success($data, $message, 201).
     *
     * Envelope: { "status": true, "code": 201, "message": $message, "data": $data }
     *
     * @warning Caller is responsible for filtering sensitive fields from $data
     *          before passing it to this method. This trait does NOT filter.
     */
    protected function created(mixed $data = null, string $message = 'Created'): ResponseInterface
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a 204 No Content response with an empty body.
     *
     * Note: setJSON() must NOT be used for 204 because HTTP 204 responses
     * must not contain a message body.
     */
    protected function noContent(): ResponseInterface
    {
        return $this->response->setStatusCode(204)->setBody('');
    }

    /**
     * Return a paginated success response with the standard envelope and meta.
     *
     * Envelope:
     * {
     *   "status": true, "code": 200, "message": $message, "data": $data,
     *   "meta": { "current_page", "per_page", "total", "total_pages" }
     * }
     *
     * @param mixed  $data    The records for the current page.
     * @param object $pager   The CI4 Pager object returned by $model->pager after calling $model->paginate().
     * @param string $message Human-readable status message.
     *
     * @warning Caller is responsible for filtering sensitive fields from $data
     *          before passing it to this method. This trait does NOT filter.
     */
    protected function paginate(mixed $data, object $pager, string $message = 'Success'): ResponseInterface
    {
        $meta = [
            'current_page' => $pager->getCurrentPage(),
            'per_page'     => $pager->getPerPage(),
            'total'        => $pager->getTotal(),
            'total_pages'  => $pager->getPageCount(),
        ];

        $body = [
            'status'  => true,
            'code'    => 200,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ];

        return $this->response->setStatusCode(200)->setJSON($body);
    }
}
