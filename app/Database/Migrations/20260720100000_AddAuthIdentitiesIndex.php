<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuthIdentitiesIndex extends Migration
{
    public function up(): void
    {
        $this->forge->addKey('secret', false, false, 'identities');
    }

    public function down(): void
    {
        $this->forge->dropKey('identities', 'secret');
    }
}
