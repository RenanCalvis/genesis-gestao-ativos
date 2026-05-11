<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LoanModel extends Model
{
    protected $table            = 'loans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id',
        'requester_establishment_id',
        'lender_establishment_id',
        'asset_id',
        'checked_out_at',
        'due_date',
        'returned_at',
    ];
    protected $useTimestamps = true;

    public function getPaginatedLoans(int $perPage = 10, ?string $search = null, ?string $status = null): array
    {
        $now = date('Y-m-d H:i:s');
        
        $this->select("
                loans.*, 
                assets.name as asset_name, 
                assets.code as asset_code, 
                req.name as requester_name, 
                len.name as lender_name,
                (CASE WHEN loans.returned_at IS NOT NULL THEN 1 ELSE 0 END) as is_returned,
                (CASE WHEN loans.returned_at IS NULL AND loans.due_date < '{$now}' THEN 1 ELSE 0 END) as is_late
            ")
            ->join('assets', 'assets.id = loans.asset_id')
            ->join('establishments as req', 'req.id = loans.requester_establishment_id')
            ->join('establishments as len', 'len.id = loans.lender_establishment_id');

        if (! empty($search)) {
            $this->groupStart()
                 ->like('assets.name', $search, 'both', null, true)
                 ->orLike('assets.code', $search, 'both', null, true)
                 ->orLike('req.name', $search, 'both', null, true)
                 ->orLike('len.name', $search, 'both', null, true)
                 ->groupEnd();
        }

        if ($status === 'late') {
            $this->where('loans.returned_at IS NULL');
            $this->where("loans.due_date < '{$now}'");
        } elseif ($status === 'returned') {
            $this->where('loans.returned_at IS NOT NULL');
        } elseif ($status === 'active') {
            $this->where('loans.returned_at IS NULL');
            $this->where("loans.due_date >= '{$now}'");
        }

        $this->orderBy('loans.checked_out_at', 'DESC');

        return $this->paginate($perPage);
    }

    public function isAssetOut(string $assetId): bool
    {
        return $this->where('asset_id', $assetId)->where('returned_at', null)->countAllResults() > 0;
    }
}
