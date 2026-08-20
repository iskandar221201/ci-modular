<?php

declare(strict_types=1);

namespace App\Modules\Users\Controllers;

use App\Shared\Controllers\BaseApiController;
use App\Shared\Exceptions\ServiceException;
use App\Shared\Exceptions\ValidationException;
use App\Modules\Users\Services\UserService;
use App\Modules\Users\Transformers\UserTransformer;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * UserController — reference CRUD implementation for the user resource.
 *
 * Demonstrates the Controller → Service → Model pattern.
 * Use this as a template when creating other resource controllers.
 *
 * All routes require apiKeyFilter authentication (configured in Routes.php).
 * Password and token fields are never exposed in responses.
 */
class UserController extends BaseApiController
{
    protected UserService $userService;

    protected UserTransformer $transformer;

    /**
     * {@inheritdoc}
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->userService = new UserService();
        $this->transformer = new UserTransformer();
    }

    /**
     * List users with optional pagination.
     *
     * GET /api/users
     * GET /api/users?per_page=10
     *
     * Response 200: JSON array of users (paginated if per_page provided).
     */
    public function index(): ResponseInterface
    {
        $filters = $this->request->getGet() ?? [];
        $result  = $this->userService->findAll($filters);

        if (isset($result['pager'])) {
            return $this->paginate(
                $this->transformer->collection($result['data']),
                $result['pager'],
            );
        }

        return $this->success($this->transformer->collection($result['data']));
    }

    /**
     * Show a single user by ID.
     *
     * GET /api/users/:id
     *
     * Response 200: JSON user object.
     * Response 404: User not found.
     */
    public function show(int $id): ResponseInterface
    {
        $user = $this->userService->findById($id);

        if ($user === null) {
            return $this->error('User not found', 404);
        }

        return $this->success($this->transformer->item($user));
    }

    /**
     * Create a new user.
     *
     * POST /api/users
     * Body: {"username":"...", "email":"...", "password":"..."}
     *
     * Response 201: JSON of the created user.
     * Response 422: Validation error.
     */
    public function create(): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true) ?? [];
            $id   = $this->userService->createUser($data);
            $user = $this->userService->findById($id);

            $this->logInfo('user.create', ['id' => $id]);

            return $this->created($this->transformer->item($user));
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->getErrors());
        } catch (ServiceException $e) {
            $this->logError('user.create.failed', [], $e);

            return $this->error($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Update an existing user.
     *
     * PUT /api/users/:id
     * Body: {"username":"..."} — partial update supported.
     *
     * Response 200: JSON of the updated user.
     * Response 404: User not found.
     * Response 422: Validation error.
     */
    public function update(int $id): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true) ?? [];
            $updated = $this->userService->updateUser($id, $data);

            return $this->success($this->transformer->item($updated), 'User updated');
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->getErrors());
        } catch (ServiceException $e) {
            $this->logError('user.update.failed', ['id' => $id], $e);

            return $this->error($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Delete a user.
     *
     * DELETE /api/users/:id
     *
     * Response 204: No content (success).
     * Response 404: User not found.
     */
    public function delete(int $id): ResponseInterface
    {
        try {
            $user = $this->userService->findById($id);

            if ($user === null) {
                return $this->error('User not found', 404);
            }

            $this->userService->delete($id);
            $this->logInfo('user.delete', ['id' => $id]);

            return $this->noContent();
        } catch (ServiceException $e) {
            $this->logError('user.delete.failed', ['id' => $id], $e);

            return $this->error($e->getMessage(), $e->getStatusCode());
        }
    }
}