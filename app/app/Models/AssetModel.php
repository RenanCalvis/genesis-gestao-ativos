<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    protected $table            = 'assets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id',
        'parent_establishment_id',
        'name',
        'code',
        'type',
        'entry_date',
        'decommissioned_at',
        'decommission_reason',
    ];
    protected $useTimestamps = true;

    public function findAllWithEstablishments(): array
    {
        return $this->select('assets.*, establishments.name AS establishment_name, establishments.type AS establishment_type')
                    ->join('establishments', 'establishments.id = assets.parent_establishment_id')
                    ->findAll();
    }
}
