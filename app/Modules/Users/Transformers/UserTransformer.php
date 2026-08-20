<?php

declare(strict_types=1);

namespace App\Modules\Users\Transformers;

use App\Shared\Transformers\BaseTransformer;

/**
 * UserTransformer — whitelist field projection for user API responses.
 *
 * Replaces the inline sanitizeUser()/sanitizeUsers() logic that previously
 * lived in UserController. Password, token and other sensitive fields are
 * never exposed.
 */
class UserTransformer extends BaseTransformer
{
    /**
     * {@inheritDoc}
     *
     * Accepts a Shield User entity (object) or an array.
     * Email lives in auth_identities and is only reachable via the entity's
     * getEmail() accessor, so it is resolved here rather than from raw array keys.
     */
    public function transform(array|object $item): array
    {
        $email = is_object($item)
            ? (method_exists($item, 'getEmail') ? $item->getEmail() : ($item->email ?? null))
            : ($item['email'] ?? null);

        return [
            'id'         => $item->id ?? ($item['id'] ?? null),
            'username'   => $item->username ?? ($item['username'] ?? null),
            'email'      => $email,
            'created_at' => $item->created_at ?? ($item['created_at'] ?? null),
            'updated_at' => $item->updated_at ?? ($item['updated_at'] ?? null),
            'active'     => $item->active ?? ($item['active'] ?? null),
        ];
    }
}