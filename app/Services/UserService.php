<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Exceptions\ValidationException;
use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use Config\AppConstants;

/**
 * UserService — reference implementation for user resource management.
 *
 * Demonstrates the Service → Model pattern using Shield's UserModel.
 * Use this as a template when creating other resource services.
 *
 * NOTE: Shield's UserModel uses its own User entity and handles
 * email identity (auth_identities table) internally via afterInsert/afterUpdate
 * model event callbacks. To trigger that behavior, we must pass a User entity
 * object to insert()/save() — NOT a plain array. This service handles that
 * difference internally.
 */
class UserService extends BaseService
{
    protected string $modelClass = UserModel::class;

    /**
     * Create a new user with email identity.
     *
     * Validates input, creates a Shield User entity, and persists it.
     * Shield's UserModel will automatically save the email/password
     * to the auth_identities table via its afterInsert callback.
     *
     * @param array{username: string, email: string, password: string} $data
     *
     * @return int|string The ID of the newly created user.
     *
     * @throws ValidationException if required fields are missing or invalid.
     * @throws ServiceException    if database insert fails.
     */
    public function createUser(array $data): int|string
    {
        $rules = [
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
        ];

        // Throws ValidationException on failure
        $this->validate($data, $rules);

        /** @var UserModel $userModel */
        $userModel = $this->model;

        // Build a Shield User entity so the afterInsert callback
        // (saveEmailIdentity) receives a proper User object with
        // email and password fields set.
        $user           = new User();
        $user->username = $data['username'];
        $user->email    = $data['email'];
        $user->password = $data['password']; // Shield hashes this automatically

        // save() delegates to insert() for new records; returns bool.
        // We use insert() directly so we can capture the inserted ID.
        $id = $userModel->insert($user, true);

        if ($id === false || $id === 0) {
            throw new ServiceException('Failed to create user record', AppConstants::HTTP_SERVER_ERROR);
        }

        // Aktifkan user langsung — admin yang membuat akun tidak perlu verifikasi email
        $createdUser = $userModel->findById($id);
        if ($createdUser !== null) {
            $userModel->activate($createdUser);
        }

        return $id;
    }

    /**
     * Update an existing user's non-credential fields.
     *
     * Validates input, strips fields that must not be updated directly
     * (id, created_at, email, password), then delegates to parent::update().
     *
     * NOTE: Email and password are NOT handled by this method because both
     * are stored in the auth_identities table and managed by Shield separately.
     * To change email/password, use Shield's dedicated credential flow.
     *
     * @param int|string           $id   The ID of the user to update.
     * @param array<string, mixed> $data Fields to update.
     *
     * @return bool true on success.
     *
     * @throws ValidationException if input fails validation.
     * @throws ServiceException    if there are no valid fields to update,
     *                             or if the DB update fails.
     */
    public function updateUser(int|string $id, array $data): mixed
    {
        $rules = [];

        // Conditional validation: only validate fields that are actually sent
        if (isset($data['username'])) {
            $rules['username'] = "required|min_length[3]|is_unique[users.username,id,{$id}]";
        }

        if (! empty($rules)) {
            // Throws ValidationException on failure
            $this->validate($data, $rules);
        }

        // Strip fields that must not be updated directly via this method.
        // Email is managed by Shield (auth_identities), password requires a dedicated hashing flow.
        foreach (['id', 'created_at', 'updated_at', 'deleted_at', 'email', 'password', 'password_hash'] as $field) {
            unset($data[$field]);
        }

        if (empty($data)) {
            throw new ServiceException('No valid fields to update', AppConstants::HTTP_BAD_REQUEST);
        }

        return parent::update($id, $data);
    }

    /**
     * Find a user by ID.
     *
     * Wraps the parent findById() with a type hint so callers
     * know they get a Shield User entity (or null).
     *
     * @param int|string $id
     */
    public function findById(int|string $id): ?User
    {
        return $this->model->find($id);
    }
}
