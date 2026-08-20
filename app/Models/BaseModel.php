<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\QueryScopesTrait;

/**
 * BaseModel
 *
 * Abstract base class for all App Models.
 * Provides default config (timestamps, soft delete, object return type),
 * optional audit-field injection, and three reusable query scopes:
 * search(), dateRange(), and active().
 *
 * NOTE: Columns `deleted_at`, `created_at`, `updated_at` must be present
 *       in each table's migration. Indexing `deleted_at` is recommended
 *       per migration for performance on soft-delete filtered queries.
 *       Fields used in search() should be indexed per child Model migration.
 */
abstract class BaseModel extends Model
{
    use QueryScopesTrait;

    // -------------------------------------------------------------------------
    // Default config overrides
    // -------------------------------------------------------------------------

    /** @var bool Enable automatic created_at / updated_at timestamps. */
    protected $useTimestamps = true;

    /** @var bool Enable soft deletes via deleted_at column. */
    protected $useSoftDeletes = true;

    /** @var string Column name for soft delete timestamp. */
    protected $deletedField = 'deleted_at';

    /** @var string Column name for created timestamp. */
    protected $createdField = 'created_at';

    /** @var string Column name for updated timestamp. */
    protected $updatedField = 'updated_at';

    /** @var string Return DB rows as objects instead of arrays. */
    protected $returnType = 'object';

    // -------------------------------------------------------------------------
    // Audit fields toggle
    // -------------------------------------------------------------------------

    /**
     * Set to true in a child Model to automatically populate
     * created_by / updated_by from the authenticated user's ID.
     *
     * If auth is not available (CLI, migration context), the fields
     * are silently skipped — no exception is thrown.
     *
     * @var bool
     */
    protected bool $useAuditFields = false;

    // -------------------------------------------------------------------------
    // Searchable fields (override in child Model)
    // -------------------------------------------------------------------------

    /**
     * List of columns to search across in search().
     * Child Models should override this to define searchable columns.
     *
     * @var array<string>
     */
    protected array $searchableFields = [];

    // -------------------------------------------------------------------------
    // Initialization — register audit callbacks
    // -------------------------------------------------------------------------

    /**
     * Register audit event callbacks if useAuditFields is enabled.
     *
     * Called automatically by CI4 Model after the constructor.
     *
     * @return void
     */
    protected function initialize(): void
    {
        if ($this->useAuditFields) {
            $this->beforeInsert[] = 'setCreatedBy';
            $this->beforeUpdate[] = 'setUpdatedBy';
        }
    }

    // -------------------------------------------------------------------------
    // Audit callback methods
    // -------------------------------------------------------------------------

    /**
     * CI4 Model beforeInsert callback.
     * Injects the authenticated user's ID into created_by if available.
     *
     * @param  array<string, mixed> $data CI4 Model callback data bag.
     * @return array<string, mixed>
     */
    protected function setCreatedBy(array $data): array
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            $data['data']['created_by'] = auth()->id();
        }

        return $data;
    }

    /**
     * CI4 Model beforeUpdate callback.
     * Injects the authenticated user's ID into updated_by if available.
     *
     * @param  array<string, mixed> $data CI4 Model callback data bag.
     * @return array<string, mixed>
     */
    protected function setUpdatedBy(array $data): array
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            $data['data']['updated_by'] = auth()->id();
        }

        return $data;
    }

    // -------------------------------------------------------------------------
    // Query scopes
    // -------------------------------------------------------------------------

    // search(), dateRange(), and active() are provided by QueryScopesTrait.
}
