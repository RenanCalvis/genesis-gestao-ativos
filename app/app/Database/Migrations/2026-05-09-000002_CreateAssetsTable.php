<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'    => 'UUID',
                'default' => new \CodeIgniter\Database\RawSql('gen_random_uuid()'),
            ],
            'parent_establishment_id' => [
                'type' => 'UUID',
                'null' => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'entry_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'decommissioned_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'decommission_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->addForeignKey('parent_establishment_id', 'establishments', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('assets');

        $this->db->query('
            ALTER TABLE assets
            ADD CONSTRAINT check_decommission
            CHECK (decommissioned_at IS NULL OR decommission_reason IS NOT NULL)
        ');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE assets DROP CONSTRAINT IF EXISTS check_decommission');

        $this->forge->dropTable('assets');
    }
}
