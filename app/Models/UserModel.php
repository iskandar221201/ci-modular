<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use App\Traits\QueryScopesTrait;

/**
 * App UserModel — extends Shield's UserModel.
 *
 * Keep this class thin. All auth-related persistence logic
 * is handled by ShieldUserModel. Add app-specific scopes,
 * searchable fields, or helper methods here only when needed.
 */
class UserModel extends ShieldUserModel
{
    use QueryScopesTrait {
        search as traitSearch;
    }

    /**
     * Fields used by QueryScopesTrait::search() for LIKE queries.
     * Inherited classes may override this when using BaseService::findAll()
     * with a search filter.
     *
     * NOTE: 'email' is stored in auth_identities.secret, not in the users
     * table. The overridden search() method handles this via a join.
     */
    protected array $searchableFields = ['username', 'email'];

    /**
     * {@inheritDoc}
     *
     * Overrides trait search() to handle 'email' field which is stored
     * in auth_identities.secret (not in the users table).
     * Joins auth_identities and maps email -> auth_identities.secret.
     */
    public function search(string $keyword, array $fields = []): static
    {
        $resolvedFields = ! empty($fields) ? $fields : $this->searchableFields;

        $hasEmail = in_array('email', $resolvedFields, true);

        if ($hasEmail) {
            $this->select("{$this->table}.*");
            $this->join(
                $this->tables['identities'],
                "{$this->tables['identities']}.user_id = {$this->table}.id",
                'left',
            );

            $resolvedFields = array_map(
                fn(string $f): string => $f === 'email' ? "{$this->tables['identities']}.secret" : $f,
                $resolvedFields,
            );
        }

        return $this->traitSearch($keyword, $resolvedFields);
    }
}
