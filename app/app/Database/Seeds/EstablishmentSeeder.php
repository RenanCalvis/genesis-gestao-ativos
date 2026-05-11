<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EstablishmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $types = ['HOSPITAL', 'CLINICA', 'LABORATORIO', 'AMBULATORIO'];
        $establishments = [];

        for ($i = 1; $i <= 15; $i++) {
            $establishments[] = [
                'id'            => sprintf('a1b2c3d4-0000-4000-8000-%012d', $i),
                'name'          => 'Estabelecimento ' . $i,
                'cnpj'          => sprintf('%014d', $i),
                'type'          => $types[$i % 4],
                'max_loan_days' => ($i % 3 === 0) ? null : ($i % 5 + 5),
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
            ];
        }

        $this->db->table('establishments')->insertBatch($establishments);
    }
}
