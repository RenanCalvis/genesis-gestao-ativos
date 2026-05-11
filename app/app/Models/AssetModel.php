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

    public function getPaginatedAssets(int $perPage = 10, ?string $search = null, ?string $status = null): array
    {
        $this->select('assets.*, establishments.name AS establishment_name, establishments.type AS establishment_type')
            ->join('establishments', 'establishments.id = assets.parent_establishment_id');

        if (! empty($search)) {
            $this->groupStart()
                ->like('assets.name', $search, 'both', null, true)
                ->orLike('assets.code', $search, 'both', null, true)
                ->groupEnd();
        }

        if ($status === 'decommissioned') {
            $this->where('assets.decommissioned_at !=', null);
        } elseif ($status !== 'all') {
            $this->where('assets.decommissioned_at', null);
        }

        $this->orderBy('assets.entry_date', 'DESC');

        return $this->paginate($perPage);
    }
}
