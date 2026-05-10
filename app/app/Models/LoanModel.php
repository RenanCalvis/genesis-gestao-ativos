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

    public function findAllWithRelations(): array
    {
        return $this->select('
                loans.*, 
                assets.name as asset_name, 
                assets.code as asset_code, 
                req.name as requester_name, 
                len.name as lender_name
            ')
            ->join('assets', 'assets.id = loans.asset_id')
            ->join('establishments as req', 'req.id = loans.requester_establishment_id')
            ->join('establishments as len', 'len.id = loans.lender_establishment_id')
            ->orderBy('loans.checked_out_at', 'DESC')
            ->findAll();
    }
}
