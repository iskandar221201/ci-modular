<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLogModel;
use Config\Services;

trait AuditTrailTrait
{
    private function resolveActor(): array
    {
        if ($apiUser = $this->getApiUserForAudit()) {
            return ['id' => $apiUser->id ?? null, 'type' => 'api'];
        }

        $auth = auth();
        if ($auth->loggedIn()) {
            return ['id' => $auth->id(), 'type' => 'web'];
        }

        return ['id' => null, 'type' => null];
    }

    private function getApiUserForAudit(): mixed
    {
        return property_exists($this, 'apiUser') ? $this->apiUser : null;
    }

    protected function auditCreate(int|string $recordId, array $newValues): void
    {
        $this->writeAudit('create', $recordId, null, $newValues);
    }

    protected function auditUpdate(int|string $recordId, array $oldValues, array $newValues): void
    {
        $changed = array_filter(
            $newValues,
            static fn ($value, $key): bool => array_key_exists($key, $oldValues) && $oldValues[$key] !== $value,
            ARRAY_FILTER_USE_BOTH
        );

        if (empty($changed)) {
            return;
        }

        $this->writeAudit('update', $recordId, array_intersect_key($oldValues, $changed), $changed);
    }

    protected function auditDelete(int|string $recordId, array $oldValues): void
    {
        $this->writeAudit('delete', $recordId, $oldValues, null);
    }

    protected function auditRestore(int|string $recordId): void
    {
        $this->writeAudit('restore', $recordId, null, null);
    }

    private function writeAudit(string $action, int|string $recordId, ?array $oldValues, ?array $newValues): void
    {
        try {
            $actor = $this->resolveActor();
            $request = Services::request();

            $payload = [
                'user_id' => $actor['id'],
                'user_type' => $actor['type'],
                'action' => $action,
                'model' => $this->modelClass ?? 'unknown',
                'record_id' => (string) $recordId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            (new AuditLogModel())->insert($payload);
        } catch (\Throwable $e) {
            log_message('error', '[AuditTrail] Failed to write audit log: ' . $e->getMessage(), [
                'action' => $action,
                'model' => $this->modelClass ?? 'unknown',
                'record_id' => (string) $recordId,
            ]);
        }
    }
}
