<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * QueryScopesTrait
 *
 * Reusable query scopes: search(), dateRange(), active().
 * Intended for use by any Model class that needs these scopes,
 * including models that do not extend App\Models\BaseModel
 * (e.g. UserModel which extends Shield's UserModel).
 *
 * The consuming class must declare:
 *   protected array $searchableFields = [];
 */
trait QueryScopesTrait
{
    /**
     * Apply a LIKE search across the given fields (or $searchableFields).
     *
     * Usage:
     *   $model->search('john', ['username', 'email'])->findAll();
     *   $model->search('john')->findAll(); // uses $this->searchableFields
     *
     * If no fields can be resolved, returns $this without adding any
     * WHERE clause (fail-silent behaviour per spec).
     *
     * @param  string        $keyword Search keyword.
     * @param  array<string> $fields  Columns to search. Falls back to $searchableFields.
     * @return static
     */
    public function search(string $keyword, array $fields = []): static
    {
        $resolvedFields = ! empty($fields) ? $fields : $this->searchableFields;

        if (empty($resolvedFields)) {
            return $this;
        }

        $this->groupStart();

        $first = true;
        foreach ($resolvedFields as $field) {
            if ($first) {
                $this->like($field, $keyword);
                $first = false;
            } else {
                $this->orLike($field, $keyword);
            }
        }

        $this->groupEnd();

        return $this;
    }

    /**
     * Apply a date-range WHERE clause on the given column.
     *
     * Usage:
     *   $model->dateRange('created_at', '2026-01-01', '2026-12-31')->findAll();
     *
     * @param  string $field Column name to filter on.
     * @param  string $from  Start date/datetime (inclusive).
     * @param  string $to    End date/datetime (inclusive).
     * @return static
     */
    public function dateRange(string $field, string $from, string $to): static
    {
        $this->where($field . ' >=', $from);
        $this->where($field . ' <=', $to);

        return $this;
    }

    /**
     * Ensure soft-delete filter is active (deleted_at IS NULL).
     *
     * CI4 soft delete already filters deleted rows by default; this method
     * makes the intent explicit and guarantees the filter is not bypassed
     * (e.g., after withDeleted() was called earlier in the chain).
     *
     * Usage:
     *   $model->active()->findAll();
     *
     * @return static
     */
    public function active(): static
    {
        return $this->withDeleted(false);
    }
}
