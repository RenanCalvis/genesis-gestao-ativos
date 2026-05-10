<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EstablishmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $establishments = [
            [
                'id'            => 'a1b2c3d4-0001-4000-8000-000000000001',
                'name'          => 'Hospital Central',
                'cnpj'          => '11222333000181',
                'type'          => 'HOSPITAL',
                'max_loan_days' => 5,
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
            ],
            [
                'id'            => 'a1b2c3d4-0002-4000-8000-000000000002',
                'name'          => 'Clínica de Radiologia Sul',
                'cnpj'          => '22333444000172',
                'type'          => 'CLINICA',
                'max_loan_days' => 15,
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
            ],
            [
                'id'            => 'a1b2c3d4-0003-4000-8000-000000000003',
                'name'          => 'Clínica Geral Norte',
                'cnpj'          => '33444555000163',
                'type'          => 'CLINICA',
                'max_loan_days' => null,
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
            ],
        ];

        $this->db->table('establishments')->insertBatch($establishments);
    }
}
