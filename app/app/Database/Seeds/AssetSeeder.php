<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $types = ['OWNED', 'RENTED', 'BORROWED'];
        $assets = [];

        for ($i = 1; $i <= 35; $i++) {
            $isDecommissioned = $i <= 7;
            $establishmentId = sprintf('a1b2c3d4-0000-4000-8000-%012d', ($i % 15) + 1);

            $assets[] = [
                'id'                      => sprintf('b9e8f7a6-0000-4000-8000-%012d', $i),
                'parent_establishment_id' => $establishmentId,
                'name'                    => 'Patrimônio ' . $i,
                'code'                    => sprintf('PAT-%04d', $i),
                'type'                    => $types[$i % 3],
                'entry_date'              => '2023-01-01 10:00:00',
                'decommissioned_at'       => $isDecommissioned ? '2023-12-01 10:00:00' : null,
                'decommission_reason'     => $isDecommissioned ? 'Equipamento obsoleto e quebrado' : null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }

        $this->db->table('assets')->insertBatch($assets);
    }
}
