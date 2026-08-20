<?php

declare(strict_types=1);

namespace App\Transformers;

abstract class BaseTransformer
{
    /**
     * Example:
     * class UserTransformer extends BaseTransformer
     * {
     *     public function transform(array $item): array
     *     {
     *         return $this->only($item, ['id', 'name', 'email']) + [
     *             'joined_at' => $item['created_at'] ?? null,
     *         ];
     *     }
     * }
     */
    abstract public function transform(array $item): array;

    public function item(array $item): array
    {
        return $this->transform($item);
    }

    public function collection(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = $this->transform((array) $item);
        }

        return $result;
    }

    protected function only(array $data, array $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }

    protected function except(array $data, array $keys): array
    {
        $excluded = array_fill_keys($keys, true);

        return array_diff_key($data, $excluded);
    }
}
