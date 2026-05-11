<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class EstablishmentModel extends Model
{
    protected $table            = 'establishments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['id', 'name', 'cnpj', 'type', 'max_loan_days'];
    protected $useTimestamps    = true;
    protected $deletedField     = 'deleted_at';

    public function getPaginatedEstablishments(int $perPage = 10, ?string $search = null): array
    {
        if (! empty($search)) {
            $this->groupStart()
                 ->like('name', $search, 'both', null, true)
                 ->orLike('cnpj', $search, 'both', null, true)
                 ->groupEnd();
        }

        $this->orderBy('created_at', 'DESC');

        return $this->paginate($perPage);
    }
}
