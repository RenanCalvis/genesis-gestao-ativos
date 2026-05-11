<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AssetModel;
use App\Models\LoanModel;
use InvalidArgumentException;

class AssetService
{
    public function __construct(
        private readonly AssetModel $assetModel = new AssetModel(),
        private readonly LoanModel $loanModel = new LoanModel(),
    ) {}

    public function decommissionAsset(string $assetId, string $reason): void
    {
        $asset = $this->assetModel->find($assetId);

        if ($asset === null) {
            throw new InvalidArgumentException('Patrimônio não encontrado.');
        }

        if ($asset['decommissioned_at'] !== null) {
            throw new InvalidArgumentException('Este patrimônio já está descomissionado.');
        }

        if ($this->loanModel->isAssetOut($assetId)) {
            throw new InvalidArgumentException('Não é possível baixar um patrimônio que está atualmente emprestado.');
        }

        $this->assetModel->update($assetId, [
            'decommissioned_at'   => date('Y-m-d H:i:s'),
            'decommission_reason' => $reason,
        ]);
    }

    public function createAsset(array $data): void
    {
        $this->assetModel->insert([
            'id'                      => $this->generateUuid(),
            'parent_establishment_id' => $data['parent_establishment_id'],
            'name'                    => $data['name'],
            'code'                    => $data['code'],
            'type'                    => $data['type'],
            'entry_date'              => $data['entry_date'],
        ]);
    }

    public function updateAsset(string $assetId, array $data): void
    {
        $asset = $this->assetModel->find($assetId);
        
        if ($asset === null) {
            throw new \InvalidArgumentException('Patrimônio não encontrado.');
        }

        $this->assetModel->update($assetId, [
            'parent_establishment_id' => $data['parent_establishment_id'],
            'name'                    => $data['name'],
            'code'                    => $data['code'],
            'type'                    => $data['type'],
            'entry_date'              => $data['entry_date'],
        ]);
    }

    private function generateUuid(): string
    {
        // geracao basica rfc 4122 v4
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }
}
