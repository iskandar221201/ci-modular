<?php

declare(strict_types=1);

namespace App\Modules\Auth\Transformers;

/**
 * AuthTransformer — shapes authentication API responses.
 *
 * Replaces the inline array-building that previously lived in AuthController.
 * Internal flags (e.g. set_cookie) are never leaked to the client.
 */
class AuthTransformer
{
    /**
     * @param array{token: string, id: int, email: string, username: string, set_cookie: bool} $payload
     *
     * @return array{token: string, id: int, email: string, username: string}
     */
    public function transformLogin(array $payload): array
    {
        unset($payload['set_cookie']);

        return $payload;
    }

    /**
     * @return array{id: int, username: string, email: string}
     */
    public function transformMe(object $user): array
    {
        return [
            'id'       => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
        ];
    }
}