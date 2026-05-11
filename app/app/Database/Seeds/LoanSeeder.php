<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $loans = [];

        for ($i = 8; $i <= 32; $i++) {
            $lenderIndex = ($i % 15) + 1;
            
            $requesterIndex = $lenderIndex + 4;
            if ($requesterIndex > 15) {
                $requesterIndex = $lenderIndex - 4;
            }

            $lenderId = sprintf('a1b2c3d4-0000-4000-8000-%012d', $lenderIndex);
            $requesterId = sprintf('a1b2c3d4-0000-4000-8000-%012d', $requesterIndex);
            $assetId = sprintf('b9e8f7a6-0000-4000-8000-%012d', $i);

            $scenario = $i % 3;

            $checkedOut = '2024-01-01 10:00:00';
            $dueDate = '2024-01-10 10:00:00';
            $returnedAt = null;

            if ($scenario === 0) {
                $returnedAt = '2024-01-08 10:00:00';
            } elseif ($scenario === 1) {
                $checkedOut = date('Y-m-d H:i:s', strtotime('-1 day'));
                $dueDate = date('Y-m-d H:i:s', strtotime('+5 days'));
            } elseif ($scenario === 2) {
                $checkedOut = date('Y-m-d H:i:s', strtotime('-20 days'));
                $dueDate = date('Y-m-d H:i:s', strtotime('-10 days'));
            }

            $loans[] = [
                'id'                         => sprintf('c8d7e6f5-0000-4000-8000-%012d', $i),
                'requester_establishment_id' => $requesterId,
                'lender_establishment_id'    => $lenderId,
                'asset_id'                   => $assetId,
                'checked_out_at'             => $checkedOut,
                'due_date'                   => $dueDate,
                'returned_at'                => $returnedAt,
                'created_at'                 => $now,
                'updated_at'                 => $now,
            ];
        }

        $this->db->table('loans')->insertBatch($loans);
    }
}
