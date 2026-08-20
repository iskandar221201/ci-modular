<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;

class CreateAuditLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 20],
            'model' => ['type' => 'VARCHAR', 'constraint' => 100],
            'record_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'old_values' => ['type' => 'TEXT', 'null' => true],
            'new_values' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('action');
        $this->forge->addKey('model');
        $this->forge->addKey('user_id');
        $this->forge->addKey('record_id');
        $this->forge->addKey(['model', 'action', 'created_at']);
        $this->forge->createTable('audit_logs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_logs', true);
    }
}
