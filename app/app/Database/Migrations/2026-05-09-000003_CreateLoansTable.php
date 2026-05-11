<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoansTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'    => 'UUID',
                'default' => new \CodeIgniter\Database\RawSql('gen_random_uuid()'),
            ],
            'requester_establishment_id' => [
                'type' => 'UUID',
                'null' => false,
            ],
            'lender_establishment_id' => [
                'type' => 'UUID',
                'null' => false,
            ],
            'asset_id' => [
                'type' => 'UUID',
                'null' => false,
            ],
            'checked_out_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'due_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'returned_at' => [
                'type' => 'TIMESTAMP',
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
        $this->forge->addForeignKey('requester_establishment_id', 'establishments', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('lender_establishment_id', 'establishments', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->addKey('requester_establishment_id');
        $this->forge->addKey('lender_establishment_id');
        $this->forge->addKey('asset_id');
        $this->forge->addKey('returned_at');
        $this->forge->addKey('due_date');

        $this->forge->createTable('loans');
    }

    public function down(): void
    {
        $this->forge->dropTable('loans');
    }
}
