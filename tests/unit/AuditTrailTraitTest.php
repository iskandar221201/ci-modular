<?php

namespace Tests\Unit;

use App\Traits\AuditTrailTrait;
use PHPUnit\Framework\TestCase;

class AuditTrailTraitTest extends TestCase
{
    public function testAuditUpdateOnlyLogsChangedFields(): void
    {
        $service = new class {
            use AuditTrailTrait;

            public array $captured = [];

            public function __construct()
            {
                $this->modelClass = 'UserModel';
            }

            public function runAuditUpdate(int|string $recordId, array $oldValues, array $newValues): void
            {
                $this->auditUpdate($recordId, $oldValues, $newValues);
            }

            protected function writeAudit(
                string $action,
                int|string $recordId,
                ?array $oldValues,
                ?array $newValues
            ): void {
                $this->captured = [
                    'action' => $action,
                    'recordId' => $recordId,
                    'oldValues' => $oldValues,
                    'newValues' => $newValues,
                ];
            }
        };

        $service->runAuditUpdate(7, ['name' => 'A', 'email' => 'a@example.com'], ['name' => 'B', 'email' => 'a@example.com']);

        $this->assertSame('update', $service->captured['action']);
        $this->assertSame(['name' => 'A'], $service->captured['oldValues']);
        $this->assertSame(['name' => 'B'], $service->captured['newValues']);
    }

    public function testAuditUpdateSkipsNoOpChanges(): void
    {
        $service = new class {
            use AuditTrailTrait;

            public bool $called = false;

            public function __construct()
            {
                $this->modelClass = 'UserModel';
            }

            public function runAuditUpdate(int|string $recordId, array $oldValues, array $newValues): void
            {
                $this->auditUpdate($recordId, $oldValues, $newValues);
            }

            protected function writeAudit(
                string $action,
                int|string $recordId,
                ?array $oldValues,
                ?array $newValues
            ): void {
                $this->called = true;
            }
        };

        $service->runAuditUpdate(8, ['name' => 'A'], ['name' => 'A']);

        $this->assertFalse($service->called);
    }
}
