<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AssetSeeder extends Seeder
{
    // UUIDs espelhados do EstablishmentSeeder para garantir integridade referencial.
    private const HOSPITAL_CENTRAL_ID       = 'a1b2c3d4-0001-4000-8000-000000000001';
    private const CLINICA_RADIOLOGIA_SUL_ID = 'a1b2c3d4-0002-4000-8000-000000000002';
    private const CLINICA_GERAL_NORTE_ID    = 'a1b2c3d4-0003-4000-8000-000000000003';

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $assets = [
            [
                'id'                       => 'b9e8f7a6-0001-4000-8000-100000000001',
                'parent_establishment_id'  => self::HOSPITAL_CENTRAL_ID,
                'name'                     => 'Desfibrilador Padrão',
                'code'                     => 'PAT-HOSP-001',
                'type'                     => 'OWNED',
                'entry_date'               => '2023-01-10 08:00:00',
                'decommissioned_at'        => null,
                'decommission_reason'      => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => 'b9e8f7a6-0002-4000-8000-100000000002',
                'parent_establishment_id'  => self::HOSPITAL_CENTRAL_ID,
                'name'                     => 'Monitor Cardíaco Bedside',
                'code'                     => 'PAT-HOSP-002',
                'type'                     => 'RENTED',
                'entry_date'               => '2023-03-15 09:00:00',
                'decommissioned_at'        => null,
                'decommission_reason'      => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                // Cenário de teste obrigatório: patrimônio descomissionado.
                // A AssetLoanService deve bloquear qualquer empréstimo deste item.
                'id'                       => 'b9e8f7a6-0003-4000-8000-100000000003',
                'parent_establishment_id'  => self::HOSPITAL_CENTRAL_ID,
                'name'                     => 'Ventilador Pulmonar Legado',
                'code'                     => 'PAT-HOSP-003',
                'type'                     => 'OWNED',
                'entry_date'               => '2020-06-01 07:00:00',
                'decommissioned_at'        => '2024-11-20 17:30:00',
                'decommission_reason'      => 'Placa lógica queimada — sem peças de reposição disponíveis no mercado.',
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => 'b9e8f7a6-0004-4000-8000-100000000004',
                'parent_establishment_id'  => self::CLINICA_RADIOLOGIA_SUL_ID,
                'name'                     => 'Tomógrafo Móvel',
                'code'                     => 'PAT-RADS-001',
                'type'                     => 'OWNED',
                'entry_date'               => '2022-09-05 08:30:00',
                'decommissioned_at'        => null,
                'decommission_reason'      => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => 'b9e8f7a6-0005-4000-8000-100000000005',
                'parent_establishment_id'  => self::CLINICA_RADIOLOGIA_SUL_ID,
                'name'                     => 'Ultrassom Portátil',
                'code'                     => 'PAT-RADS-002',
                'type'                     => 'BORROWED',
                'entry_date'               => '2024-02-20 10:00:00',
                'decommissioned_at'        => null,
                'decommission_reason'      => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => 'b9e8f7a6-0006-4000-8000-100000000006',
                'parent_establishment_id'  => self::CLINICA_GERAL_NORTE_ID,
                'name'                     => 'Aparelho de ECG 12 Canais',
                'code'                     => 'PAT-CGNP-001',
                'type'                     => 'OWNED',
                'entry_date'               => '2023-07-11 08:00:00',
                'decommissioned_at'        => null,
                'decommission_reason'      => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => 'b9e8f7a6-0007-4000-8000-100000000007',
                'parent_establishment_id'  => self::CLINICA_GERAL_NORTE_ID,
                'name'                     => 'Bomba de Infusão Volumétrica',
                'code'                     => 'PAT-CGNP-002',
                'type'                     => 'RENTED',
                'entry_date'               => '2024-05-03 09:00:00',
                'decommissioned_at'        => null,
                'decommission_reason'      => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => 'b9e8f7a6-0008-4000-8000-100000000008',
                'parent_establishment_id'  => self::CLINICA_GERAL_NORTE_ID,
                'name'                     => 'Oxímetro de Pulso Portátil',
                'code'                     => 'PAT-CGNP-003',
                'type'                     => 'BORROWED',
                'entry_date'               => '2024-08-19 11:00:00',
                'decommissioned_at'        => null,
                'decommission_reason'      => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
        ];

        $this->db->table('assets')->insertBatch($assets);
    }
}
